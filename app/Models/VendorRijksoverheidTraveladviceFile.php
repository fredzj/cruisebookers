<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorRijksoverheidTraveladviceFile extends Model
{
    protected $table = 'vendor_rijksoverheid_nl_traveladvice_files';
    protected $fillable = ['id', 'filedescription', 'fileurl'];
}
