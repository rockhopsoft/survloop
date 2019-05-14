<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessSite extends Model
{
    protected $table      = 'SL_SessSite';
    protected $primaryKey = 'SiteSessID';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'SiteSessIPaddy', 
		'SiteSessUserID', 
		'SiteSessIsMobile', 
		'SiteSessBrowser', 
		'SiteSessZoomPref', 
    ];
    
    // END SurvLoop auto-generated portion of Model
    
}
