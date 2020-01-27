<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLTokens extends Model
{
    protected $table      = 'sl_tokens';
    protected $primaryKey = 'tok_id';
    public $timestamps    = true;
    protected $fillable   = 
    [    
        'tok_type', 
        'tok_user_id', 
        'tok_tree_id', 
        'tok_core_id', 
        'tok_tok_token',
    ];
    
}
