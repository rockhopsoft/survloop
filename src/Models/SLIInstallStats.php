<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLIInstallStats extends Model
{
    protected $table      = 'sli_install_stats';
    protected $primaryKey = 'inst_statid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'inst_statinstall_id', 
		'inst_statdate', 
		'inst_statdb_tables', 
		'inst_statdb_fields', 
		'inst_statsurveys', 
		'inst_statsurvey_nodes', 
		'inst_statsurvey_nodes_mult', 
		'inst_statsurvey_nodes_open', 
		'inst_statsurvey_nodes_numb', 
		'inst_statpages', 
		'inst_statpage_nodes', 
		'inst_statusers', 
		'inst_statsurvey1_complete', 
		'inst_statcode_lines_controllers', 
		'inst_statcode_lines_views', 
		'inst_statbytes_controllers', 
		'inst_statbytes_database', 
		'inst_statbytes_views', 
		'inst_statbytes_uploads', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
