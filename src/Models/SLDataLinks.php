<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLDataLinks extends Model
{
    use Cachable;

    protected $table      = 'sl_data_links';
    protected $primaryKey = 'data_link_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
        'data_link_tree', 
        'data_link_table', 
    ];
}
