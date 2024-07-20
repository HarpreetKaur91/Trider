<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','complete_address','landmark','city','state','pincode'];
    protected $hidden = ['created_at','updated_at'];

    public function provider(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
