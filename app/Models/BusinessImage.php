<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessImage extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','business_profile_id','business_image'];

    protected $hidden = ['created_at','updated_at'];
}
