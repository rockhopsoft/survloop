<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLUsersActivity extends Model
{
    protected $table         = 'sl_users_activity';
    protected $primaryKey     = 'user_act_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'user_act_user', 
        'user_act_curr_page', 
        'user_act_val', 
    ];
}
