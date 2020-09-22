<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLIRequests extends Model
{
    protected $table      = 'sli_requests';
    protected $primaryKey = 'reqid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'reqtitle', 
		'reqdescription', 
		'reqis_coder', 
		'reqemail', 
		'reqis_mobile', 
		'requser_id', 
		'reqsubmission_progress', 
		'requnique_str', 
		'reqtree_version', 
		'reqversion_ab', 
		'reqip_addy', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
