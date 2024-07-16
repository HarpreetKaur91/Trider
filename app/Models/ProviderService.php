<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id','service_id','bio'];

    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
