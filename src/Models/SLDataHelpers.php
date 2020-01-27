<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDataHelpers extends Model
{
    protected $table         = 'sl_data_helpers';
    protected $primaryKey     = 'data_help_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'data_help_tree', 
        'data_help_parent_table', 
        'data_help_table', 
        'data_help_key_field', 
        'data_help_value_field', 
    ];
}
