<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSess extends Model
{
    protected $table      = 'SL_Sess';
    protected $primaryKey = 'SessID';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'SessUserID', 
		'SessTree', 
		'SessCoreID', 
		'SessIsActive', 
		'SessCurrNode', 
		'SessLoopRootJustLeft', 
		'SessAfterJumpTo', 
		'SessIsMobile', 
		'SessBrowser', 
		'SessIP', 
    ];
    
    // END SurvLoop auto-generated portion of Model
    
}
