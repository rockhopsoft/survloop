<?= '<'.'?'.'php' ?> namespace App\Models\{{ $GLOBALS["DB"]->sysOpts["cust-abbr"] }};
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-model-gen.blade.php

use Illuminate\Database\Eloquent\Model;

class {{ $tblClean }} extends Model
{
	protected $table 		= '{{ $tblName }}';
	protected $primaryKey 	= '{{ $tbl->TblAbbr }}ID';
	public $timestamps 		= true;
	protected $fillable 	= 
	[	{!! $modelFile !!}
	];
}
