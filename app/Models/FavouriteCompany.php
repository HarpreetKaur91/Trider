<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteCompany extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','company_id'];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function company(){
        return $this->belongsTo('App\Models\User','company_id','id');
    }
}
