<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLConditionsVals extends Model
{
    use Cachable;

    protected $table         = 'sl_conditions_vals';
    protected $primaryKey     = 'cond_val_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'cond_val_cond_id', 
        'cond_val_value', 
    ];
}
