<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLTree extends Model
{
    protected $table      = 'sl_tree';
    protected $primaryKey = 'tree_id';
    public $timestamps    = true;
    protected $fillable   = 
    [
		'tree_database', 
		'tree_user', 
		'tree_type', 
		'tree_name', 
		'tree_desc', 
		'tree_slug', 
		'tree_root', 
		'tree_first_page', 
		'tree_last_page', 
		'tree_core_table', 
		'tree_opts', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
