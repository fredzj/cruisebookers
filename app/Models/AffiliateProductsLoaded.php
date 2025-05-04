<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateProductsLoaded extends Model
{
    protected $table = 'affiliate_products_loaded_searchpage';

    protected $fillable = [
        'slug',
        'name',
        'price',
        'merchant_id',
        // Add other columns as needed
    ];

    public function additionalData()
    {
        return $this->hasOne(ProductPageData::class, 'slug', 'slug');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
}
