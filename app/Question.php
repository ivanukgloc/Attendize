<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Hyn\Tenancy\Traits\TenantAwareConnection;

/**
 * Description of Questions.
 *
 * @author Dave
 */
class Question extends MyBaseModel
{
    use SoftDeletes, TenantAwareConnection;

    /**
     * The events associated with the question.
     *
     * @access public
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events()
    {
        return $this->belongsToMany(\App\Event::class);
    }

    /**
     * The type associated with the question.
     *
     * @access public
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function question_type()
    {
        return $this->belongsTo(\App\QuestionType::class);
    }

    public function answers()
    {
        return $this->hasMany(\App\QuestionAnswer::class);
    }

    /**
     * The options associated with the question.
     *
     * @access public
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function options()
    {
        return $this->hasMany(\App\QuestionOption::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(\App\Ticket::class);
    }

    /**
     * Scope a query to only include active questions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsEnabled($query)
    {
        return $query->where('is_enabled', 1);
    }
}
