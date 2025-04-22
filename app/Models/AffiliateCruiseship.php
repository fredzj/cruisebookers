<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCruiseship extends Model
{
    protected $table = 'affiliate_cruiseships';

    protected $fillable = [
        'class',
        'cruiseline_id',
        'description',
        'facilities',
        'introduction',
        'is_blocked',
        'length',
        'name',
        'name_alt',
        'number_of_crew',
        'number_of_decks',
        'number_of_passengers',
        'number_of_passengers_max',
        'number_of_rooms',
        'slug',
        'stars',
        'type',
        'year_built',
        'year_first_in_service',
        'year_last_refurbishment',
    ];

    public $timestamps = false; // Disable timestamps if the table has created_at and updated_at columns

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    /**
     * Scope a query to only include non-blocked cruiseships.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonBlocked($query)
    {
        return $query->where('is_blocked', 0);
    }
    /**
     * Define a relationship with the AffiliateCruiseline model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cruiseline()
    {
        return $this->belongsTo(AffiliateCruiseline::class, 'cruiseline_id');
    }
}
