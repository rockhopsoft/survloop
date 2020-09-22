<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLThreadLikes extends Model
{
    protected $table      = 'sl_thread_likes';
    protected $primaryKey = 'thrd_likid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'thrd_likcomment_id', 
		'thrd_likuser_id', 
		'thrd_liklike', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
