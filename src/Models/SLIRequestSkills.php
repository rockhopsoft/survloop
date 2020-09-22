<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLIRequestSkills extends Model
{
    protected $table      = 'sli_request_skills';
    protected $primaryKey = 'req_sklid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'req_sklrequest_id', 
		'req_sklskill', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
