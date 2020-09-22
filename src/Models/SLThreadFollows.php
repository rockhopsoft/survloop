<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLThreadFollows extends Model
{
    protected $table      = 'sl_thread_follows';
    protected $primaryKey = 'thrd_flwid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'thrd_flwcomment_id', 
		'thrd_flwuser_id', 
		'thrd_flwfollow_type', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
