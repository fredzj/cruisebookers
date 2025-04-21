<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorRijksoverheidTraveladvice extends Model
{
    protected $table = 'vendor_rijksoverheid_nl_traveladvice';
    protected $fillable = ['id', 'canonical', 'introduction', 'location', 'title', 'modificationdate', 'modifications', 'authorities', 'creators', 'lastmodified', 'issued', 'available', 'rightsholders'];
}