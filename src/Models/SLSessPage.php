<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessPage extends Model
{
    protected $table      = 'SL_SessPage';
    protected $primaryKey = 'SessPageID';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'SessPageSessID', 
		'SessPageURL', 
    ];
    
    // END SurvLoop auto-generated portion of Model
    
}
