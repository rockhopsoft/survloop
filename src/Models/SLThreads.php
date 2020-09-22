<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLThreads extends Model
{
    protected $table      = 'sl_threads';
    protected $primaryKey = 'thrdid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'thrdname', 
		'thrddiscuss_total', 
		'thrddiscuss_last', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
