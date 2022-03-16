<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment  extends Model
{
    

    protected $guarded = ['id', 'created_at', 'updated_at'];
    

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
    
}
