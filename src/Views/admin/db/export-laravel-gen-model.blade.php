<?= '<'.'?'.'php' ?> namespace App\Models;
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class {{ $tblClean }} extends Model
{
    protected $table      = '{{ $tblName }}';
    protected $primaryKey = '{{ $tbl->tbl_abbr }}id';
    public $timestamps    = true;
    protected $fillable   = 
    [    {!! $modelFile !!}
    ];
    
    // END Survloop auto-generated portion of Model
    
}
