<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDatabases extends Model
{
    protected $table         = 'sl_databases';
    protected $primaryKey     = 'db_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'db_user', 
        'db_prefix', 
        'db_name', 
        'db_desc', 
        'db_mission', 
        'db_opts', 
        'db_tables', 
        'db_fields', 
    ];
}
