<?php namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class SLCampaignClicks extends Model
{
    protected $table      = 'sl_campaign_clicks';
    protected $primaryKey = 'camp_clkid';
    public $timestamps    = true;
    protected $fillable   = 
    [    
		'camp_clkcampaign_id', 
		'camp_clkfrom_url', 
		'camp_clkto_url', 
    ];
    
    // END Survloop auto-generated portion of Model
    
}
