<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNode extends Model
{
    protected $table         = 'sl_node';
    protected $primaryKey     = 'node_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'node_tree', 
        'node_parent_id', 
        'node_parent_order', 
        'node_type', 
        'node_promp_text', 
        'node_prompt_notes', 
        'node_prompt_after', 
        'node_internal_notes', 
        'node_response_set', 
        'node_default', 
        'node_data_branch', 
        'node_data_store', 
        'node_text_suggest', 
        'node_char_limit', 
        'node_likes', 
        'node_dislikes', 
        'node_opts', 
    ];
}
