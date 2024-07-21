<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteBusiness extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','business_id'];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function business(){
        return $this->belongsTo('App\Models\User','business_id','id');
    }

}
