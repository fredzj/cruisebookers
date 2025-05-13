<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateNetworkMerchant extends Model
{
    protected $table = 'affiliate_networks_merchants';

    protected $fillable = [
        'affiliate_network_code',
        'description',
        'domain_name',
        'is_member_anvr',
        'is_member_cf',
        'is_member_sgr',
        'name',
        'rating_value',
        'review_count',
        'slug',
        'type',
        'url_merchant_logo'
    ];

    /**
     * Define a relationship to the AffiliateProductsLoaded model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(AffiliateProductsLoaded::class, 'merchant_id');
    }

    /**
     * Scope a query to only include non-blocked merchants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonBlocked($query)
    {
        return $query->where('is_blocked', 0);
    }
}