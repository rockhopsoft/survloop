<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLCaches extends Model
{
    protected $table      = 'sl_caches';
    protected $primaryKey = 'cach_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'cach_type', 
		'cach_tree_id', 
		'cach_rec_id', 
		'cach_key', 
        'cach_value', 
    ];
     
    // END Survloop auto-generated portion of Model
    
}
