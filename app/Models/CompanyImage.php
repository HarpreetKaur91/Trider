<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyImage extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','company_profile_id','image'];
    protected $hidden = ['created_at','updated_at'];
}
