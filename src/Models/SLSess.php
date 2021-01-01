<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSess extends Model
{
    protected $table      = 'sl_sess';
    protected $primaryKey = 'sess_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'sess_user_id', 
		'sess_tree', 
		'sess_core_id', 
        'sess_is_active', 
		'sess_curr_node', 
		'sess_loop_root_just_left', 
		'sess_after_jump_to', 
		'sess_is_mobile', 
		'sess_browser', 
		'sess_ip', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
