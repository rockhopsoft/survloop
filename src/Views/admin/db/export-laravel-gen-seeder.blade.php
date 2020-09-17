<?= '<'.'?'.'php' ?> 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-seeder.blade.php

namespace Database\Seeders;

use Auth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class {{ str_replace('_', '', $GLOBALS['SL']->dbRow->db_prefix) }}Seeder extends Seeder
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