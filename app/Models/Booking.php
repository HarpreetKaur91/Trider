<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','business_id','order_no','booking_date','booking_time','total_amount','grand_amount','amount','tax','payment_status','booking_status'];
    protected$hidden = ['created_at','updated_at'];
    public function booking_services(){
        return $this->hasMany('App\Models\BookingService');
    }

    public function business(){
        return $this->hasOne('App\Models\User','id','business_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
