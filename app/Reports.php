<?php

namespace App;

use App\Customer;
use Illuminate\Support\Facades\Auth;

class Reports
{
    private $totalUsers;

    /**
     * @return integer
     */
    public function getTotalUsers()
    {
        if (is_null($this->totalUsers)) {
            $this->totalUsers = Customer::count();
        }

        return $this->totalUsers;
    }
}
