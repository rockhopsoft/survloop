<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLAddyGeo extends Model
{
    protected $table         = 'sl_addy_geo';
    protected $primaryKey     = 'ady_geo_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'ady_geo_address', 
        'ady_geo_lat', 
        'ady_geo_long', 
    ];
}
