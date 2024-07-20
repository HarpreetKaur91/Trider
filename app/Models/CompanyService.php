<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyService extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','service_id','bio'];
    protected $hidden = ['created_at','updated_at'];
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
