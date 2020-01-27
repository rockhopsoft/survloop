<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLSessEmojis extends Model
{
    protected $table         = 'sl_sess_emojis';
    protected $primaryKey     = 'sess_emo_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'sess_emo_user_id',
        'sess_emo_tree_id', 
        'sess_emo_rec_id', 
        'sess_emo_def_id',
    ];
}
