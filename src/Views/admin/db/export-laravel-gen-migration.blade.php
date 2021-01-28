<?= '<'.'?'.'php' ?> 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Create{{ str_replace('_', '', $GLOBALS['SL']->sysOpts['cust-abbr']) }}Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET SESSION sql_require_primary_key=0');
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
