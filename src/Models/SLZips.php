<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLZips extends Model
{
	use Cachable;

    protected $table      = 'sl_zips';
    protected $primaryKey = 'zip_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'zip_zip', 
		'zip_lat', 
		'zip_long', 
		'zip_city', 
		'zip_state', 
		'zip_county', 
    ];
}
