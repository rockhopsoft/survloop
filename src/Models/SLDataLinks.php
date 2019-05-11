<?php namespace Storage\App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDataLinks extends Model
{
    protected $table         = 'SL_DataLinks';
    protected $primaryKey     = 'DataLinkID';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'DataLinkTree', 
        'DataLinkTable', 
    ];
}
