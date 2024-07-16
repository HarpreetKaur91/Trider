<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAddress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','complete_address','landmark','city','state','pincode'];

    public function provider(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
