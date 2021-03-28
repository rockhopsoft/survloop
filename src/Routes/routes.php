<?php
/**
  * routes.php registers all the paths used by Survloop behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// If running on a system with the Laravel Debugbar,
// then un-commenting this line will [temporarily] disable it.
//  \Debugbar::disable();

use RockHopSoft\Survloop\Controllers\Globals\GlobalsMicroTime;
$GLOBALS["SL-Micro"] = new GlobalsMicroTime;

Route::middleware(['web'])->group(function () {

    require_once('routes-auth.php');

    require_once('routes-core.php');

    require_once('routes-tree.php');

});
