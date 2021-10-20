<?php namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SLEmails extends Model
{
    use Cachable;

    protected $table = 'sl_emails';
    protected $primaryKey = 'email_id';
    
    protected $fillable = [
        'email_tree', 
        'email_type', 
        'email_name', 
        'email_subject', 
        'email_body', 
        'email_opts', 
        'email_tot_sent', 
        'email_attach',
    ];
    
    
}
