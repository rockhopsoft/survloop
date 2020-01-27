<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNodeSaves extends Model
{
    protected $table         = 'sl_node_saves';
    protected $primaryKey     = 'node_save_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'node_save_session', 
        'node_save_loop_item_id', 
        'node_save_node', 
        'node_save_version_ab', 
        'node_save_tbl_fld', 
        'node_save_new_val', 
    ];
}
