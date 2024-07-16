<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteProvider extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','provider_id'];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function provider(){
        return $this->belongsTo('App\Models\User','provider_id','id');
    }
    
}
