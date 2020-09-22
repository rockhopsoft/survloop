<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessSite extends Model
{
    protected $table      = 'sl_sess_site';
    protected $primaryKey = 'site_sess_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'site_sess_ip_addy', 
		'site_sess_user_id', 
		'site_sess_is_mobile', 
		'site_sess_browser', 
		'site_sess_zoom_pref', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
