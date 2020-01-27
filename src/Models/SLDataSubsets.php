<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDataSubsets extends Model
{
    protected $table         = 'sl_data_subsets';
    protected $primaryKey     = 'data_sub_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'data_sub_tree', 
        'data_sub_tbl', 
        'data_sub_tbl_lnk', 
        'data_sub_sub_tbl', 
        'data_sub_sub_lnk', 
        'data_sub_auto_gen', 
    ];
}
