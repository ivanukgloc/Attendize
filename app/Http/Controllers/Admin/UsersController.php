<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Attendize\Utils;
use App\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controllers\ResourceController;

class UsersController extends Controller
{
    use ResourceController;

    /**
     * @var string
     */
    protected $resourceAlias = 'admin.users';

    /**
     * @var string
     */
    protected $resourceRoutesAlias = 'admin::users';

    /**
     * Fully qualified class name
     *
     * @var string
     */
    protected $resourceModel = Customer::class;

    /**
     * @var string
     */
    protected $resourceTitle = 'Users';

    /**
     * Used to validate store.
     *
     * @return array
     */
    private function resourceStoreValidationData()
    {
        return [
            'rules' => [
                'first_name' => 'required|min:3|max:255',
                'last_name' => 'required|min:3|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'logo_number' => 'required|in:' . implode(',', Utils::getLogosNumber()),
            ],
            'messages' => [],
            'attributes' => [],
        ];
    }

    /**
     * Used to validate update.
     *
     * @param $record
     * @return array
     */
    private function resourceUpdateValidationData($record)
    {
        return [
            'rules' => [
                'first_name' => 'required|min:3|max:255',
                'last_name' => 'required|min:3|max:255',
                'email' => 'required|email|max:255|unique:users,email,'.$record->id,
                'logo_number' => 'required|in:' . implode(',', Utils::getLogosNumber()),
            ],
            'messages' => [],
            'attributes' => [],
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $record
     * @return array
     */
    private function getValuesToSave(Request $request, $record = null)
    {
        $creating = is_null($record);
        $values = [];

        $values['currency_id'] = config('attendize.default_currency');
        $values['timezone_id'] = config('attendize.default_timezone');

        $values['first_name'] = $request->input('first_name', '');
        $values['last_name'] = $request->input('last_name', '');
        $values['email'] = $request->input('email', '');
        $values['logo_number'] = Utils::getValidLogoNumber($request->input('logo_number', 1));

        return $values;
    }

    private function alterValuesToSave(Request $request, $values)
    {
        if (array_key_exists('password', $values)) {
            if (!empty($values['password'])) {
                $values['password'] = Hash::make($values['password']);
            } else {
                unset($values['password']);
            }
        }

        return $values;
    }

    /**
     * @param $record
     * @return bool
     */
    private function checkDestroy($record)
    {
        if (Auth::user()->id == $record->id) {
            flash()->error('You can not delete your own user.');

            return false;
        }

        return true;
    }

    /**
     * Retrieve the list of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $show
     * @param string|null $search
     * @return \Illuminate\Support\Collection
     */
    private function getSearchRecords(Request $request, $show = 15, $search = null)
    {
        if (! empty($search)) {
            return $this->getResourceModel()::where('name', 'LIKE', '%'.$search.'%')->paginate($show);
        }

        return $this->getResourceModel()::paginate($show);
    }
}
