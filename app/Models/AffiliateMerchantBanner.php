<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateMerchantBanner extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'affiliate_networks_merchants_ads';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'tinyInteger';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'merchant_slug',
        'size',
        'banner',
        'timestamp',
    ];

    /**
     * Define a relationship to the Merchant model (if applicable).
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}