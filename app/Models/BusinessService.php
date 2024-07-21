<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessService extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','business_profile_id','service_id'];

    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
