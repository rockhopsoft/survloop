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
