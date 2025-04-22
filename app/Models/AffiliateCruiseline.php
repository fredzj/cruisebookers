<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCruiseline extends Model
{
    protected $table = 'affiliate_cruiselines';

    protected $fillable = [
        'description',
        'introduction',
        'is_blocked',
        'lead_paragraph',
        'name',
        'slogan',
        'slug',
        'url_logo',
    ];

    public $timestamps = false; // Disable timestamps if the table has created_at and updated_at columns

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    /**
     * Scope a query to only include non-blocked cruiselines.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonBlocked($query)
    {
        return $query->where('is_blocked', 0);
    }

    /**
     * Define a relationship with the Cruiseship model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cruiseships()
    {
        return $this->hasMany(Cruiseship::class, 'cruiseline_id');
    }
}