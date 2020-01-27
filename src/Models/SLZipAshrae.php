<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLZipAshrae extends Model
{
    protected $table         = 'sl_zip_ashrae';
    protected $primaryKey     = 'ashr_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'ashr_zone', 
        'ashr_state', 
        'ashr_county', 
    ];
}
