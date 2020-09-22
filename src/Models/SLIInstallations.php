<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLIInstallations extends Model
{
    protected $table      = 'sli_installations';
    protected $primaryKey = 'instid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'instname', 
		'instdesc', 
		'insturl', 
		'instlogo_url', 
		'instuser_id', 
		'instip_addy', 
		'instsubmission_progress', 
		'instversion_ab', 
		'insttree_version', 
		'instunique_str', 
		'instis_mobile', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
