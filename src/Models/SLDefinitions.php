<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDefinitions extends Model
{
    protected $table         = 'sl_definitions';
    protected $primaryKey     = 'def_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'def_database', 
        'def_set', 
        'def_subset', 
        'def_order', 
        'def_is_active', 
        'def_value', 
        'def_description', 
    ];
}
