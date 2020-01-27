<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNodeSavesPage extends Model
{
    protected $table         = 'sl_node_saves_page';
    protected $primaryKey     = 'page_save_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'page_save_session', 
        'page_save_node', 
        'page_save_loop_item_id', 
    ];
}
