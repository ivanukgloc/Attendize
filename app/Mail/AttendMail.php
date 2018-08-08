<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Auth;

class AttendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->data = $request;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build($attendee)
    {
        return $this->view('view.name');

        // $email = $this->from('')
        //            ->to($attendee->email)
        //            ->view('Mailers.TicketMailer.SendAttendeeInvite')
        //            ->with('msg', $this->data)
        //            ->attach($request->file('file'), [
        //                 'as' => rand(100,100000).'.'.$request->file('file')->getClientOriginalExtension(),
        //                 'mime' => File::mimeType($request->file('file'))
        //         ]);

        // return $email;
    }
}
