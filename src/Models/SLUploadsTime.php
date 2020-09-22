<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLUploadsTime extends Model
{
    protected $table      = 'sl_uploads_time';
    protected $primaryKey = 'up_ti_id';
    public $timestamps    = true;
    protected $fillable   = 
    [
		'up_ti_upload_id', 
		'up_ti_timestamp', 
		'up_ti_description', 
		'up_ti_link_fld_id', 
		'up_ti_link_rec_id', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
