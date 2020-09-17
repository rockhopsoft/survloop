<?= '<'.'?'.'php' ?> 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create{{ str_replace('_', '', $GLOBALS['SL']->sysOpts['cust-abbr']) }}Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
    {!! $migratFileUp !!}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    {!! $migratFileDown !!}
    }
}
