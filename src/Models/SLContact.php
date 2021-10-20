<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLContact extends Model
{
    use Cachable;

    protected $table         = 'sl_contact';
    protected $primaryKey     = 'cont_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'cont_type', 
        'cont_flag', 
        'cont_email', 
        'cont_subject', 
        'cont_body', 
    ];
}
