<?= '<'.'?'.'php' ?> 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-seeder.blade.php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class {{ str_replace('_', '', $GLOBALS['SL']->dbRow->DbPrefix) }}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
@if (!isset($wholeSeed) || $wholeSeed)
    {!! $dumpOut["Seeders"] !!}
    }
}
@endif