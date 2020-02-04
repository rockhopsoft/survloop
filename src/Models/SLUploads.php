<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLUploads extends Model
{
    protected $table         = 'sl_uploads';
    protected $primaryKey     = 'up_id';
    public $timestamps         = true;
    protected $fillable     = 
    [
		'up_tree_id', 
		'up_core_id', 
		'up_type', 
		'up_privacy', 
		'up_title', 
		'up_desc', 
		'up_upload_file', 
		'up_stored_file', 
		'up_video_link', 
		'up_video_duration', 
		'up_node_id', 
		'up_link_fld_id', 
		'up_link_rec_id', 
    ];
}
