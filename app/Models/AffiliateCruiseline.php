<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCruiseline extends Model
{
    protected $table = 'affiliate_cruiselines';
    protected $fillable = ['description', 'introduction', 'is_blocked', 'lead_paragraph', 'name', 'slogan', 'slug', 'url_logo'];
}