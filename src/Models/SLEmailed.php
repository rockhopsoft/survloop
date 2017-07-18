<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLEmailed extends Model
{
    protected $table = 'SL_Emailed';
    protected $primaryKey = 'EmailedID';
    
    protected $fillable = [
        'EmailedTree', 
        'EmailedRecID', 
        'EmailedEmailID', 
        'EmailedDate', 
        'EmailedTo', 
        'EmailedFromUser', 
        'EmailedCustomSpots', 
        'EmailedOpts',  
    ];
    
    
}
