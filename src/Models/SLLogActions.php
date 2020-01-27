<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLLogActions extends Model
{
    protected $table         = 'sl_log_actions';
    protected $primaryKey     = 'log_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'log_user', 
        'log_database', 
        'log_table', 
        'log_field', 
        'log_action', 
        'log_old_name', 
        'log_new_name', 
    ];
}
