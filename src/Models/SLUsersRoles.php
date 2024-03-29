<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLUsersRoles extends Model
{
    use Cachable;

    protected $table         = 'sl_users_roles';
    protected $primaryKey     = 'role_user_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'role_user_uid', 
        'role_user_rid', 
    ];
}
