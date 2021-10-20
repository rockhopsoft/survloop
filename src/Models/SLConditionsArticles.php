<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLConditionsArticles extends Model
{
    use Cachable;

    protected $table         = 'sl_conditions_articles';
    protected $primaryKey     = 'article_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'article_cond_id', 
        'article_url', 
        'article_title', 
    ];
}
