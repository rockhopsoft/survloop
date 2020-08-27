<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SLEmailed extends Model
{
    protected $table = 'sl_emailed';
    protected $primaryKey = 'emailed_id';
    
    protected $fillable = [
        'emailed_tree', 
        'emailed_rec_id', 
        'emailed_email_id', 
        'emailed_to', 
        'emailed_to_user', 
        'emailed_from_user', 
        'emailed_subject', 
        'emailed_body', 
        'emailed_opts',  
        'emailed_attach',
    ];
    
    
}
