<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLConditionsNodes extends Model
{
    protected $table         = 'sl_conditions_nodes';
    protected $primaryKey     = 'cond_node_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'cond_node_cond_id', 
        'cond_node_node_id', 
        'cond_node_loop_id', 
    ];
}
