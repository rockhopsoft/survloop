<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessLoops extends Model
{
    protected $table         = 'sl_sess_loops';
    protected $primaryKey     = 'sess_loop_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'sess_loop_sess_id', 
        'sess_loop_name', 
        'sess_loop_item_id', 
    ];
}
