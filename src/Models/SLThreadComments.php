<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLThreadComments extends Model
{
    protected $table      = 'sl_thread_comments';
    protected $primaryKey = 'thrd_cmtid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'thrd_cmtthread_id', 
		'thrd_cmtuser_id', 
		'thrd_cmtsess_id', 
		'thrd_cmtreply_to', 
		'thrd_cmtreply_root', 
		'thrd_cmtdepth', 
		'thrd_cmttot_likes', 
		'thrd_cmttot_dislikes', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
