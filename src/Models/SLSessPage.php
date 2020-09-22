<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessPage extends Model
{
    protected $table      = 'sl_sess_page';
    protected $primaryKey = 'sess_page_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'sess_page_sess_id', 
		'sess_page_url', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
