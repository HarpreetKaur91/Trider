<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','company_name','company_phone_number','gst_number','pan_card_number'];
    protected $hidden = ['created_at','updated_at'];
}
