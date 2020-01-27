<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLBusRules extends Model
{
    protected $table         = 'sl_bus_rules';
    protected $primaryKey     = 'rule_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'rule_database', 
        'rule_statement', 
        'rule_constraint', 
        'rule_tables', 
        'rule_fields', 
        'rule_is_app_orient', 
        'rule_is_relation', 
        'rule_test_on', 
        'rule_phys', 
        'rule_logic', 
        'rule_rel', 
        'rule_action', 
    ];
}
