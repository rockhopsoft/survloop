<?php
/**
  * routes-core.php registers all the paths used by core Survloop behavior.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */

use RockHopSoft\Survloop\Controllers\Survloop;
use RockHopSoft\Survloop\Controllers\SurvRoutes;
use RockHopSoft\Survloop\Controllers\Admin\AdminController;
use RockHopSoft\Survloop\Controllers\Admin\AdminTreeController;
use RockHopSoft\Survloop\Controllers\Auth\ForgotPasswordController;
use RockHopSoft\Survloop\Controllers\Auth\UpdatePasswordController;
use RockHopSoft\Survloop\Controllers\Auth\AuthController;

/**
 * Home Page Routes
 */
Route::post('/',     [Survloop::class, 'loadPageHome']);
Route::get('/',      [Survloop::class, 'loadPageHome']);
Route::post('/home', [Survloop::class, 'loadPageHome']);
Route::get('/home',  [Survloop::class, 'loadPageHome']);


/**
 * Common AJAX/jQuery Requests
 */
Route::get('/search-bar', [Survloop::class, 'searchBar']);
Route::get('/search-run', [Survloop::class, 'searchRun']);
Route::get('/search-results/{treeID}', [Survloop::class, 'searchResultsAjax']);

Route::post('/ajax',         [Survloop::class, 'ajaxChecks']);
Route::get('/ajax',          [Survloop::class, 'ajaxChecks']);
Route::post('/ajax/{type}',  [Survloop::class, 'ajaxChecks']);
Route::get('/ajax/{type}',   [Survloop::class, 'ajaxChecks']);

Route::middleware(['auth'])->group(function () {

    Route::post('/ajadm',        [AdminController::class, 'ajaxChecksAdmin']);
    Route::get('/ajadm',         [AdminController::class, 'ajaxChecksAdmin']);
    Route::post('/ajadm/{type}', [AdminController::class, 'ajaxChecksAdmin']);
    Route::get('/ajadm/{type}',  [AdminController::class, 'ajaxChecksAdmin']);

});

Route::get('/js-load-menu',  [Survloop::class, 'jsLoadMenu']);


/**
 * Survloop Post-Install System Initialization
 */
Route::get('/fresh/creator',   [AdminTreeController::class, 'freshUser']);
Route::post('/fresh/database', [AdminTreeController::class, 'freshDB']);
Route::get('/fresh/database',  [AdminTreeController::class, 'freshDB']);
Route::post('/fresh/survey',   [AdminTreeController::class, 'freshUX']);
Route::get('/fresh/survey',    [AdminTreeController::class, 'freshUX']);


/**
 * Core User Authentication
 */
Route::get('/chk-email',   [Survloop::class, 'chkEmail']);
Route::get('/after-login', [AuthController::class, 'afterLogin']);
Route::get('/logout',      [AuthController::class, 'getLogout']);

Route::get('/password/email-sent', [AuthController::class, 'printPassResetSent']);
/*
//Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    //->name('password.email');
Route::get('/password/reset', function () {
    return redirect('/forgot-password');
});
*/


/**
 * Core User Authentication
 */
Route::get('/user/{uname}',    [Survloop::class, 'showProfile']);
Route::get('/profile/{uname}', [Survloop::class, 'showProfile']);


Route::middleware(['auth'])->group(function () {

    Route::get('/my-profile',          [Survloop::class, 'showMyProfile']);
    Route::post('/change-my-password', [UpdatePasswordController::class, 'runUpdate']);
    Route::get('/change-my-password',  [UpdatePasswordController::class, 'runUpdate']);
    Route::get('/user/{uname}/stats',  [Survloop::class, 'profileStats']);

});

Route::group(['middleware' => ['auth', 'password.confirm']], function () {

    Route::post('/my-profile/manage',   [Survloop::class, 'editProfile']);
    Route::get('/my-profile/manage',    [Survloop::class, 'editProfile']);
    Route::post('/user/{uname}/manage', [Survloop::class, 'editProfile']);
    Route::get('/user/{uname}/manage',  [Survloop::class, 'editProfile']);

});


/**
 * Survloop Auth Helpers
 */
Route::get('/restart',  [Survloop::class, 'restartSess']);
Route::get('/sessDump', [Survloop::class, 'sessDump']);
Route::get('/test',     [Survloop::class, 'testHome']);
Route::get('/time-out', [Survloop::class, 'timeOut']);
Route::get('/spinner',  [Survloop::class, 'spinnerUrl']);

Route::get('/email-confirm/{token}/{tokenB}', [Survloop::class, 'processEmailConfirmToken']);


/**
 * System-Level Client-Side File Delivery
 */
Route::get('/css-reload',             [AdminController::class, 'getSysCssJs']);
Route::get('/sys{which}.min.{type}',  [SurvRoutes::class, 'getSysFileMin']);
Route::get('/sys{which}.{type}',      [SurvRoutes::class, 'getSysFile']);
Route::get('/tree-{treeID}.js',       [SurvRoutes::class, 'getSysTreeJs']);
Route::get('/sys/dyna/{file}.{type}', [SurvRoutes::class, 'getDynaFile']);
Route::get('/gen-kml/{kmlfile}.kml',  [SurvRoutes::class, 'getKml']);
Route::get('/img/user/{user}',        [SurvRoutes::class, 'getProfilePhoto']);


/**
 * External Packages
 */
Route::get('/fonts/{file}',                 [SurvRoutes::class, 'getFont']);
Route::get('/jquery.min.js',                [SurvRoutes::class, 'getJquery']);
Route::get('/jquery-ui.min.{type}',         [SurvRoutes::class, 'getJqueryUi']);
Route::get('/bootstrap.min.{type}',         [SurvRoutes::class, 'getBootstrap']);
Route::get('/css/fork-awesome.min.css',     [SurvRoutes::class, 'getFontAwesome']);
Route::get('/css/fork-awesome.min.css.map', [SurvRoutes::class, 'getFontAwesomeMap']);

Route::get(
    '/survloop-libraries/state-flags/{stateFile}.{ext}',
    [SurvRoutes::class, 'getStateFlag']
);

Route::get(
    '/vendor/rockhopsoft/survloop/src/Public/jquery-ui-1.12.1/images/{file}',
    [SurvRoutes::class, 'catchJqueryUiMappingError']
);

Route::get('/summernote.min.js',   [SurvRoutes::class, 'getSummernoteJs']);
Route::get('/summernote.css',      [SurvRoutes::class, 'getSummernoteCss']);
Route::get('/font/summernote.eot', [SurvRoutes::class, 'getSummernoteEot']);
Route::get('/Chart.bundle.min.js', [SurvRoutes::class, 'getChartJs']);
Route::get('/plotly.min.js',       [SurvRoutes::class, 'getPlotlyJs']);


/**
 * System Services
 */
Route::get('/survloop-stats.json', [Survloop::class, 'getJsonSurvStats']);
Route::get('test-route-call',      [Survloop::class, 'testRouteCall']);
