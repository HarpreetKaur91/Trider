<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','booking_id','service_id','qty','hours','days','total_amount','amount'];
    protected$hidden = ['created_at','updated_at'];

    public function business(){
        return $this->hasOne('App\Models\User','id','business_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id',);
    }

    public function service(){
        return $this->hasOne('App\Models\Service','id','service_id');
    }
}
