<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLDesignTweaks extends Model
{
    protected $table         = 'sl_design_tweaks';
    protected $primaryKey     = 'twk_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'twk_version_ab', 
        'twk_submission_progress', 
        'twk_user_id', 
        'twk_ip_addy', 
    ];
}
