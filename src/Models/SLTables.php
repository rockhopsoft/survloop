<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLTables extends Model
{
    protected $table         = 'sl_tables';
    protected $primaryKey     = 'tbl_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'tbl_database', 
        'tbl_abbr', 
        'tbl_name', 
        'tbl_eng', 
        'tbl_desc', 
        'tbl_notes', 
        'tbl_type', 
        'tbl_group', 
        'tbl_ord', 
        'tbl_opts', 
        'tbl_extend', 
        'tbl_num_fields', 
        'tbl_num_foreign_keys', 
        'tbl_num_foreign_in', 
    ];
}
