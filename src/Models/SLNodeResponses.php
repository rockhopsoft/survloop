<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNodeResponses extends Model
{
    protected $table         = 'sl_node_responses';
    protected $primaryKey     = 'node_res_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'node_res_node', 
        'node_res_ord', 
        'node_res_eng', 
        'node_res_value', 
        'node_res_show_kids', 
        'node_res_mut_ex'
    ];
}
