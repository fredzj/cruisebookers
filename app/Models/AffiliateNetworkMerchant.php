<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateNetworkMerchant extends Model
{
    protected $table = 'affiliate_networks_merchants';
    protected $fillable = ['name', 'url_merchant_logo', 'slug', 'domain_name', 'description', 'type', 'is_member_anvr', 'is_member_cf', 'is_member_sgr'];
}
