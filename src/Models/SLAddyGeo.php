<?php namespace SurvLoop\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLAddyGeo extends Model
{
    protected $table         = 'SL_AddyGeo';
    protected $primaryKey     = 'AdyGeoID';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'AdyGeoAddress', 
        'AdyGeoLat', 
        'AdyGeoLong', 
    ];
}
