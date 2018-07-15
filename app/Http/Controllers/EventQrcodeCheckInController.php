<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Attendee;
use App\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JavaScript;

class EventQrcodeCheckInController extends Controller
{
    /**
     * Show the check-in page
     *
     * @param $event_id
     * @return \Illuminate\View\View
     */
    public function showCheckIn($event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        JavaScript::put([
            'qrcodeCheckInRoute' => route('postQRCodeCheckInAttendee', ['event_id' => $event->id])
        ]);

        return view('ManageEvent.QrcodeCheckIn', compact('event'));
    }

    /**
     * Check in an attendee
     *
     * @param $event_id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postCheckInAttendee($event_id, Request $request)
    {
        $event = Event::scope()->findOrFail($event_id);

        $qrcodeToken = $request->get('qrcode_token');

        $attendee = Attendee::scope()->withoutCancelled()
            ->join('tickets', 'tickets.id', '=', 'attendees.ticket_id')
            ->where(function ($query) use ($event, $qrcodeToken) {
                $query->where('attendees.event_id', $event->id)
                    ->where('attendees.private_reference_number', $qrcodeToken);
            })->select([
                'attendees.id',
                'attendees.order_id',
                'attendees.first_name',
                'attendees.last_name',
                'attendees.email',
                'attendees.reference',
                'attendees.arrival_time',
                'attendees.has_arrived',
                'tickets.title as ticket',
            ])->first();

        if (is_null($attendee)) {
            return response()->json(['status' => 'error', 'message' => trans("Controllers.invalid_ticket_error")]);
        }

        $relatedAttendesCount = Attendee::where('id', '!=', $attendee->id)
            ->where([
                'order_id'    => $attendee->order_id,
                'has_arrived' => false
            ])->count();

        $appendedText = '';
        if ($relatedAttendesCount >= 1) {
            $confirmOrderTicketsRoute = route('confirmCheckInOrderTickets', [$event->id, $attendee->order_id]);

            $appendedText = '<br><br><form class="ajax" action="' . $confirmOrderTicketsRoute . '" method="POST">' . csrf_field() . '<button class="btn btn-primary btn-sm" type="submit"><i class="ico-ticket"></i> '.trans("Controllers.check_in_all_tickets").'</button></form>';
        }

        if ($attendee->has_arrived) {
            return response()->json([
                'status'  => 'error',
                'message' => trans("Controllers.attendee_already_checked_in", ["time"=>$attendee->arrival_time->format(env("DEFAULT_DATETIME_FORMAT"))]) . $appendedText
            ]);
        }

        Attendee::find($attendee->id)->update(['has_arrived' => true, 'arrival_time' => Carbon::now()]);

        return response()->json([
            'status'  => 'success',
            'message' => trans("Controllers.attendee_check_in_success", ["name"=> $attendee->first_name.' '.$attendee->last_name, "ref"=>$attendee->reference, "ticket"=>$attendee->ticket]). $appendedText
        ]);
    }

    /**
     * Confirm tickets of same order.
     *
     * @param $event_id
     * @param $order_id
     * @return \Illuminate\Http\Response
     */
    public function confirmOrderTickets($event_id, $order_id)
    {
        $updateRowsCount = Attendee::scope()->where([
            'event_id'     => $event_id,
            'order_id'     => $order_id,
            'has_arrived'  => false,
            'arrival_time' => Carbon::now(),
        ])
            ->update(['has_arrived' => true, 'arrival_time' => Carbon::now()]);

        return response()->json([
            'message' => trans("Controllers.num_attendees_checked_in", ["num"=>$updateRowsCount])
        ]);
    }
}
