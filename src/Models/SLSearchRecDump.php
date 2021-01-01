<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSearchRecDump extends Model
{
    protected $table         = 'sl_search_rec_dump';
    protected $primaryKey     = 'sch_rec_dmp_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'sch_rec_dmp_tree_id', 
        'sch_rec_dmp_rec_id', 
        'sch_rec_dmp_rec_dump',
        'sch_rec_dmp_perms',
    ];
}
