<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Hyn\Tenancy\Traits\TenantAwareConnection;


class AccountPaymentGateway extends MyBaseModel
{

    use softDeletes, TenantAwareConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_gateway_id',
        'config'
    ];

    /**
     * Account associated with gateway
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account() {
        return $this->belongsTo(\App\Account::class);
    }

    /**
     * Parent payment gateway
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_gateway()
    {
        return $this->belongsTo(\App\PaymentGateway::class, 'payment_gateway_id', 'id');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getConfigAttribute($value) {
        return json_decode($value, true);
    }

    public function setConfigAttribute($value) {
        $this->attributes['config'] = json_encode($value);
    }
}
