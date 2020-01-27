<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLFields extends Model
{
    protected $table         = 'sl_fields';
    protected $primaryKey     = 'fld_id';
    public $timestamps         = true;
    protected $fillable     = 
    [    
        'fld_database', 
        'fld_table', 
        'fld_ord', 
        'fld_spec_type', 
        'fld_spec_source', 
        'fld_name', 
        'fld_eng', 
        'fld_alias', 
        'fld_desc', 
        'fld_notes', 
        'fld_foreign_table', 
        'fld_foreign_min', 
        'fld_foreign_max', 
        'fld_foreign2_min', 
        'fld_foreign2_max', 
        'fld_values', 
        'fld_default', 
        'fld_is_index', 
        'fld_type', 
        'fld_data_type', 
        'fld_data_length', 
        'fld_data_decimals', 
        'fld_char_support', 
        'fld_input_mask', 
        'fld_display_format', 
        'fld_key_type', 
        'fld_key_struct', 
        'fld_edit_rule', 
        'fld_unique', 
        'fld_null_support', 
        'fld_values_entered_by', 
        'fld_required', 
        'fld_compare_same', 
        'fld_compare_other', 
        'fld_compare_value', 
        'fld_operate_same', 
        'fld_operate_other', 
        'fld_operate_value', 
        'fld_opts', 
    ];
}
