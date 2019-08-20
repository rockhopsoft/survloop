<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLCaches extends Model
{
    protected $table      = 'SL_Caches';
    protected $primaryKey = 'CachID';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'CachType', 
		'CachTreeID', 
		'CachRecID', 
		'CachKey', 
        'CachValue', 
    ];
     
    // END SurvLoop auto-generated portion of Model
    
}
