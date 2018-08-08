<?php

namespace App;
use Hyn\Tenancy\Traits\TenantAwareConnection;

class QuestionOption extends MyBaseModel
{
    use TenantAwareConnection;
    /**
     * Indicates if the model should be timestamped.
     *
     * @access public
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @access protected
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The question associated with the question option.
     *
     * @access public
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(\App\Question::class);
    }
}
