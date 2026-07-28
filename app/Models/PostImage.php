<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = [
        'post_id',
        'url',
        'public_id', 
        'position', 
        'width', 
        'height'
        ];
}
