<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLImages extends Model
{
    protected $table         = 'sl_images';
    protected $primaryKey     = 'img_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'img_database_id', 
        'img_user_id', 
        'img_file_orig', 
        'img_file_loc', 
        'img_full_filename', 
        'img_title', 
        'img_credit', 
        'img_credit_url', 
        'img_node_id', 
        'img_type', 
        'img_file_size',
        'img_width', 
        'img_height', 
    ];
}
