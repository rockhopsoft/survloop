<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNodes extends Model
{
    protected $table      = 'sl_nodes';
    protected $primaryKey = 'nodeid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'nodetree', 
		'nodeparent_id', 
		'nodeparent_order', 
		'nodetype', 
		'nodeprompt_text', 
		'nodeprompt_notes', 
		'nodeprompt_after', 
		'nodeinternal_notes', 
		'noderesponse_set', 
		'nodedefault', 
		'nodedata_branch', 
		'nodedata_store', 
		'nodetext_suggest', 
		'nodechar_limit', 
		'nodelikes', 
		'nodedislikes', 
		'nodeopts', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
