<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAddress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','business_profile_id','address_line_one','address_line_two','city','state','pincode','latitude','longitude'];

    public function provider(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
