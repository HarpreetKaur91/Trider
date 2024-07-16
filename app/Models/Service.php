<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id','name','logo','description','status'];

    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id','id');
    }

    public function children()
    {
        return $this->hasMany(self::class,'parent_id','id')->select('id','parent_id','name','description','image');
    }


}