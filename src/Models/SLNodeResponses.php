<?php namespace Storage\App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLNodeResponses extends Model
{
    protected $table         = 'SL_NodeResponses';
    protected $primaryKey     = 'NodeResID';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'NodeResNode', 
        'NodeResOrd', 
        'NodeResEng', 
        'NodeResValue', 
        'NodeResShowKids', 
        'NodeResMutEx'
    ];
}
