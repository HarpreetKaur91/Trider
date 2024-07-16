<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirebaseNotification extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','udid','firebase_token','device_type'];
}
