<?php namespace Storage\App\Models;

use Illuminate\Database\Eloquent\Model;

class SLTokens extends Model
{
    protected $table      = 'SL_Tokens';
    protected $primaryKey = 'TokID';
    public $timestamps    = true;
    protected $fillable   = 
    [    
        'TokType', 
        'TokUserID', 
        'TokTreeID', 
        'TokCoreID', 
        'TokTokToken',
    ];
    
}
