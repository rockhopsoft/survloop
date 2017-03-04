<?= '<'.'?'.'php' ?> 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class {{ str_replace('_', '', $GLOBALS['SL']->dbRow->DbPrefix) }}CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    {!! $migrationFileUp !!}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    {!! $migrationFileDown !!}
    }
}
