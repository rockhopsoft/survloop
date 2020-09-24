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
use RockHopSoft\Survloop\Controllers\Globals\GlobalsMicroTime;
$GLOBALS["SL-Micro"] = new GlobalsMicroTime;

Route::group(['middleware' => ['web']], function () {

    $path = 'RockHopSoft\\Survloop\\Controllers\\';
    
    require_once('routes-core.php');

    require_once('routes-tree.php');

    require_once('routes-admin.php');

    require_once('routes-admin-db.php');

    require_once('routes-admin-tree.php');

    require_once('routes-admin-slug.php');

    require_once('routes-slug.php');

});
