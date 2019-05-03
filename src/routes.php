<?php

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

Route::group(['middleware' => ['web']], function () {
    
    Route::post('/',             'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::get( '/',             'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::post('/sub',          'SurvLoop\\Controllers\\SurvLoop@mainSub');
    
    Route::get( '/sortLoop',     'SurvLoop\\Controllers\\SurvLoop@sortLoop');
    Route::get( '/holdSess',     'SurvLoop\\Controllers\\SurvLoop@holdSess');
    Route::get( '/restart',      'SurvLoop\\Controllers\\SurvLoop@restartSess');
    Route::get( '/sessDump',     'SurvLoop\\Controllers\\SurvLoop@sessDump');
    Route::get( '/test',         'SurvLoop\\Controllers\\SurvRoutes@testHome');
    
    Route::get( '/switch/{treeID}/{cid}',  'SurvLoop\\Controllers\\SurvLoop@switchSess');
    Route::get( '/delSess/{treeID}/{cid}', 'SurvLoop\\Controllers\\SurvLoop@delSess');
    Route::get( '/cpySess/{treeID}/{cid}', 'SurvLoop\\Controllers\\SurvLoop@cpySess');
    
    Route::get( '/{abbr}/uploads/{file}', 'SurvLoop\\Controllers\\SurvLoop@getUploadFile');
    
    // main survey process for primary database, primary tree
    Route::post('/u/{nodeSlug}', 'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    Route::get( '/u/{nodeSlug}', 'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    
    // survey process for any database or tree
    Route::get( '/start/{treeSlug}',         'SurvLoop\\Controllers\\SurvLoop@loadNodeTreeURL');
    Route::get( '/start-{cid}/{treeSlug}', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@loadNodeTreeURLedit', 
        'middleware' => ['auth']
    ]);
    Route::post('/u/{treeSlug}/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    Route::get( '/u/{treeSlug}/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    
    Route::get( '/defer/{treeID}/{nodeID}',  'SurvLoop\\Controllers\\SurvLoop@deferNode');
    Route::get( '/up/{treeSlug}/{cid}/{upID}', 'SurvLoop\\Controllers\\SurvLoop@retrieveUpload');
    
    Route::get( '/search-bar',               'SurvLoop\\Controllers\\SurvLoop@searchBar');
    Route::get( '/search-results/{treeID}',  'SurvLoop\\Controllers\\SurvLoop@searchResultsAjax');
    
    Route::get( '/records-full/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxRecordFulls');
    Route::get( '/record-prevs/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxRecordPreviews');
    Route::get( '/record-check/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxMultiRecordCheck');
    Route::get( '/record-graph/{gType}/{treeID}/{nID}',     'SurvLoop\\Controllers\\SurvLoop@ajaxGraph');
    Route::get( '/widget-custom/{treeID}/{nID}',            'SurvLoop\\Controllers\\SurvLoop@widgetCust');
    
    Route::get( '/ajax-get-flds/{treeID}',                  'SurvLoop\\Controllers\\SurvLoop@getSetFlds');
    Route::get( '/ajax-get-flds/{treeID}/{rSet}',           'SurvLoop\\Controllers\\SurvLoop@getSetFlds');
    Route::get( '/ajax-emoji-tag/{treeID}/{recID}/{defID}', 'SurvLoop\\Controllers\\SurvLoop@ajaxEmojiTag');

    Route::post('/ajax',         'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::get( '/ajax',         'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::post('/ajax/{type}',  'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::get( '/ajax/{type}',  'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::post('/ajadm',        'SurvLoop\Controllers\Admin\AdminController@ajaxChecksAdmin');
    Route::get( '/ajadm',        'SurvLoop\Controllers\Admin\AdminController@ajaxChecksAdmin');
    Route::post('/ajadm/{type}', 'SurvLoop\Controllers\Admin\AdminController@ajaxChecksAdmin');
    Route::get( '/ajadm/{type}', 'SurvLoop\Controllers\Admin\AdminController@ajaxChecksAdmin');
    
    Route::get( '/{treeSlug}-xml-all',         'SurvLoop\\Controllers\\SurvLoop@xmlAll');
    Route::get( '/{treeSlug}-xml-example',      'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/{treeSlug}-xml-example.xml',  'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/schema/{treeSlug}/xml',       'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/{treeSlug}-xml-schema',       'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/{treeSlug}-xml-schema.xsd',   'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/{treeSlug}-report-xml/{cid}', 'SurvLoop\\Controllers\\SurvLoop@xmlByID');
    Route::get( '/xml-example',                 'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/xml-schema',                  'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    
    Route::get( '/fresh/creator',  'SurvLoop\Controllers\Admin\AdminTreeController@freshUser');
    Route::post('/fresh/database', 'SurvLoop\Controllers\Admin\AdminTreeController@freshDB');
    Route::get( '/fresh/database', 'SurvLoop\Controllers\Admin\AdminTreeController@freshDB');
    Route::post('/fresh/survey',   'SurvLoop\Controllers\Admin\AdminTreeController@freshUX');
    Route::get( '/fresh/survey',   'SurvLoop\Controllers\Admin\AdminTreeController@freshUX');
    
    
    ///////////////////////////////////////////////////////////
    
    Route::post('/login',      [ 'as' => 'login', 'uses' => 'SurvLoop\\Controllers\\Auth\\AuthController@postLogin' ]);
    Route::get( '/login',      [ 'as' => 'login', 'uses' => 'SurvLoop\\Controllers\\Auth\\AuthController@getLogin' ]);
    Route::post('/register',   'SurvLoop\\Controllers\\Auth\\SurvRegisterController@register');
    Route::get( '/register',   'SurvLoop\\Controllers\\Auth\\AuthController@getRegister');
    Route::post('/afterLogin', 'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/afterLogin', 'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/logout',     'SurvLoop\\Controllers\\Auth\\AuthController@getLogout');
    Route::get( '/chkEmail',   'SurvLoop\\Controllers\\SurvLoop@chkEmail');
    
    Route::get( '/time-out',   'SurvLoop\\Controllers\\SurvLoop@timeOut');
    Route::get( '/survloop-stats.json',   'SurvLoop\\Controllers\\SurvLoop@getJsonSurvStats');
    
    Route::get( '/email-confirm/{token}/{tokenB}', 'SurvLoop\\Controllers\\SurvLoop@processEmailConfirmToken');
    
    Route::get( '/js-load-menu', 'SurvLoop\\Controllers\\SurvLoop@jsLoadMenu');
    
    Route::get( '/sys{which}.min.{type}', 'SurvLoop\\Controllers\\SurvRoutes@getSysFileMin');
    Route::get( '/sys{which}.{type}',     'SurvLoop\\Controllers\\SurvRoutes@getSysFile');
    Route::get( '/tree-{treeID}.js',      'SurvLoop\\Controllers\\SurvRoutes@getSysTreeJs');
    Route::get( '/dyna-{file}.{type}',    'SurvLoop\\Controllers\\SurvRoutes@getDynaFile');
    Route::get( '/gen-kml/{kmlfile}.kml', 'SurvLoop\\Controllers\\SurvRoutes@getKml');
    
    Route::get( '/jquery.min.js',         'SurvLoop\\Controllers\\SurvRoutes@getJquery');
    Route::get( '/jquery-ui.min.{type}',  'SurvLoop\\Controllers\\SurvRoutes@getJqueryUi');
    Route::get( '/bootstrap.min.{type}',  'SurvLoop\\Controllers\\SurvRoutes@getBootstrap');
    
    Route::get( '/css/fork-awesome.min.css', 'SurvLoop\\Controllers\\SurvRoutes@getFontAwesome');
    Route::get( '/fonts/{file}',             'SurvLoop\\Controllers\\SurvRoutes@getFont');
    
    Route::get( '/summernote.min.js',     'SurvLoop\\Controllers\\SurvRoutes@getSummernoteJs');
    Route::get( '/summernote.css',        'SurvLoop\\Controllers\\SurvRoutes@getSummernoteCss');
    Route::get( '/font/summernote.eot',   'SurvLoop\\Controllers\\SurvRoutes@getSummernoteEot');
    
    Route::get( '/Chart.bundle.min.js',   'SurvLoop\\Controllers\\SurvRoutes@getChartJs');
    Route::get( '/plotly.min.js',         'SurvLoop\\Controllers\\SurvRoutes@getPlotlyJs');
    
    ///////////////////////////////////////////////////////////
    
    Route::get( '/admin', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageDashboard', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dash', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageDashboard', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageDashboard', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/profile/{uname}', [
        'uses'       => 'SurvLoop\\Controllers\\SurvLoop@showProfile',
        'middleware' => 'auth'
    ]);
    Route::get( '/profile/{uname}', 'SurvLoop\\Controllers\\SurvLoop@showProfile');
    Route::post('/my-profile', [
        'uses'       => 'SurvLoop\\Controllers\\SurvLoop@showMyProfile',
        'middleware' => 'auth'
    ]);
    Route::get( '/my-profile', [
        'uses'       => 'SurvLoop\\Controllers\\SurvLoop@showMyProfile',
        'middleware' => 'auth'
    ]);
    Route::post('/change-my-password', [
        'uses'       => 'SurvLoop\\Controllers\\Auth\\UpdatePasswordController@runUpdate',
        'middleware' => 'auth'
    ]);
    Route::get( '/change-my-password', [
        'uses'       => 'SurvLoop\\Controllers\\Auth\\UpdatePasswordController@runUpdate',
        'middleware' => 'auth'
    ]);
    Route::post('password/email', 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get( '/password/email', 'SurvLoop\\Controllers\\Auth\\AuthController@printPassReset');
    Route::get( '/password/reset', 'SurvLoop\\Controllers\\Auth\\AuthController@printPassReset');
    Route::get('password/reset/{token}', 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm')->name('password.reset');
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.update');
    //Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

    
    Route::post('/home', 'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::get( '/home', 'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    
    
    ///////////////////////////////////////////////////////////
    
    Route::get( '/dashboard/logs', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@logsOverview', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dashboard/logs/session-stuff', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@logsSessions', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/systems-check', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@systemsCheck',    
        'middleware' => ['auth']
    ]);
    Route::post('/dashboard/systems-check', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@systemsCheck',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/subs', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/subs/all', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/subs/unpublished', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@listUnpublished',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/subs/incomplete', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@listSubsIncomplete',    
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/subs/{treeID}/{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@printSubView',    
        'middleware' => ['auth']
    ]);
    Route::get( '/dashboard/subs/{treeID}/{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@printSubView',    
        'middleware' => ['auth']
    ]);
    
    
    Route::get( '/dashboard/contact', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@manageContact', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/emails', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/emails', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/email/{emailID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@manageEmailsPost', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/email/{emailID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@manageEmailsForm', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/pages/snippets/{blurbID}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminController@blurbEditSave', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/pages/snippets/{blurbID}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminController@blurbEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/users/email', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminController@userEmailing', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/users', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminController@userManage', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/users', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminController@userManage', 
        'middleware' => ['auth']
    ]);
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::post('/dashboard/settings', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@sysSettings',
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/settings', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@sysSettings',
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/settings-raw', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@sysSettingsRaw',
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/settings-raw', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@sysSettingsRaw',
        'middleware' => ['auth']
    ]);
    
    
    
    
    Route::get( '/tree/{treeSlug}', 'SurvLoop\Controllers\Admin\AdminTreeController@adminPrintFullTreePublic');
    
    Route::post('/dashboard/surveys/list', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treesList',
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surveys/list', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treesList',
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/settings', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeSettings', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/settings', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeSettings', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/map', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/map', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/sessions', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeSessions',
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/sessions/graph-daily', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeSessGraphDaily',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/sessions/graph-durations', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeSessGraphDurations',    
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/stats', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@treeStats', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/data', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/data', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/map/node/{nID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/map/node/{nID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/xmlmap', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/{treeSlug}-xmlmap', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@xmlmapInner', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/xmlmap', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surv-{treeID}/xmlmap/node/{nodeIN}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surv-{treeID}/xmlmap/node/{nodeIN}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surveys/switch/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@switchTreeAdmin', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surveys/switch', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@switchTreeAdmin', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/surveys/new', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@newTree', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/surveys/new', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@newTree', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/pages/add-{addPageType}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@autoAddPages', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/page/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/page/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/page/{treeID}/map', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/page/{treeID}/map', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/pages', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@pagesList', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/pages', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@pagesList', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/reports', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@reportsList', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/reports', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@reportsList', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/redirects', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@redirectsList', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/redirects', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@redirectsList', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/pages/snippets', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@blurbsList', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/pages/snippets', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@blurbsList', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/pages/menus', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@navMenus', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/pages/menus', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@navMenus', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/images/gallery', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@imgGallery', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/images/gallery', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@imgGallery', 
        'middleware' => ['auth']
    ]);
    
    
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::get( '/db/{database}', 'SurvLoop\Controllers\Admin\AdminDBController@adminPrintFullDBPublic');
    
    Route::get( '/dashboard/db', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/all', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@full', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/field-matrix', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldMatrix', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/addTable', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@addTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/addTable', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@addTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/sortTable', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@tblSort', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/table/{tblName}/edit', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@editTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/table/{tblName}/edit', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@editTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/table/{tblName}/sort', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fldSort', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/table/{tblName}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@viewTable', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@editField', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@editField', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@addTableFld', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/field/{tblAbbr}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@addTableFld', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/ajax-field/{FldID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldAjax', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/fieldDescs', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldDescs', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dashboard/db/fieldDescs', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldDescs', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/ajax/tblFldSelT/{rT}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminDBController@tblSelector'
    ]);
    
    Route::get( '/dashboard/db/ajax/tblFldSelF/{rF}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminDBController@fldSelector'
    ]);
    
    Route::get( '/dashboard/db/ajax/getSetFlds/{rSet}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminDBController@getSetFlds'
    ]);
    
    Route::get( '/dashboard/db/ajax/getSetFldVals/{FldID}', [
        'uses' => 'SurvLoop\Controllers\Admin\AdminDBController@getSetFldVals'
    ]);
    
    Route::post('/dashboard/db/fieldXML/save', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldXMLsave', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/fieldXML', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@fieldXML', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/definitions', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@definitions', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/definitions/add/{subset}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/definitions/add', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/definitions/edit/{defID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub/{subset}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/edit-sub/{defID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/definitions/sort/{subset}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@defSort', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/bus-rules', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@businessRules', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/add', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@ruleAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/bus-rules/add', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@ruleAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/edit/{ruleID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@ruleEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/bus-rules/edit/{ruleID}',    [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@ruleEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/diagrams', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@diagrams', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/network-map', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@networkMap', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/workflows', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@workflows', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/conds', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/conds', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/conds/add', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@condAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/conds/add', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@condAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/conds/edit/{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@condEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/conds/edit/{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@condEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/export', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@printExport', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/export/laravel', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@printExportLaravel', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/export/laravel/table-model/{tbl}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@refreshTableModel', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/install', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@autoInstallDatabase', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/install', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@autoInstallDatabase', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/export/dump', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@exportDump', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/sl/export', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@printExportPackage', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/sl/export/laravel', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@printExportPackageLaravel', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/db/db', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@manualMySql', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/db/db', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDatabaseInstall@manualMySql', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/switch/{dbID}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@switchDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/switch', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminDBController@switchDB', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/new', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@newDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/db/new', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminTreeController@newDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/systems-update', [
        'uses'       => 'SurvLoop\Controllers\SystemUpdate@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/css-reload', 'SurvLoop\Controllers\Admin\AdminController@getCSS');
    
    // survey process for any admin tree
    Route::get( '/dashboard/start/{treeSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadNodeTreeURL', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dashboard/start-{cid}/{treeSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadNodeTreeURLedit', 
        'middleware' => ['auth']
    ]);
    Route::post('/dash-sub', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@postNodeURL', 
        'middleware' => ['auth']
    ]);
    Route::post('/dash/u/{treeSlug}/{nodeSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadNodeURL', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dash/u/{treeSlug}/{nodeSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadNodeURL', 
        'middleware' => ['auth']
    ]);
    
    // views include full, public, pdf, full-pdf, xml, full-xml
    Route::post('/{pageSlug}/read-{cid}/full/t-{token}', 'SurvLoop\\Controllers\\SurvLoop@tokenByID');
    Route::get( '/{pageSlug}/read-{cid}/full/t-{token}', 'SurvLoop\\Controllers\\SurvLoop@tokenByID');
    Route::get( '/{treeSlug}/read-{cid}/xml',            'SurvLoop\\Controllers\\SurvLoop@xmlByID');
    //Route::get( '/{treeSlug}/read-{cid}/json',           'SurvLoop\\Controllers\\SurvLoop@xmlByID');
    Route::post('/{pageSlug}/read-{cid}/{view}',         'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::get( '/{pageSlug}/read-{cid}/{view}',         'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::post('/{pageSlug}/read-{cid}',                'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::get( '/{pageSlug}/read-{cid}',                'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::post('/{pageSlug}/u-{cid}',                   'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::get( '/{pageSlug}/u-{cid}',                   'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::post('/{pageSlug}/readi-{cid}/{view}',        'SurvLoop\\Controllers\\SurvLoop@loadPageURLrawID');
    Route::get( '/{pageSlug}/readi-{cid}/{view}',        'SurvLoop\\Controllers\\SurvLoop@loadPageURLrawID');
    Route::post('/{pageSlug}/readi-{cid}',               'SurvLoop\\Controllers\\SurvLoop@loadPageURLrawID');
    Route::get( '/{pageSlug}/readi-{cid}',               'SurvLoop\\Controllers\\SurvLoop@loadPageURLrawID');
    Route::post('/{pageSlug}',                           'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::get( '/{pageSlug}',                           'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    
    Route::post('/dash/{pageSlug}/read-{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageURL', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dash/{pageSlug}/read-{cid}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageURL', 
        'middleware' => ['auth']
    ]);
    Route::post('/dash/{pageSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageURL', 
        'middleware' => ['auth']
    ]);
    Route::get( '/dash/{pageSlug}', [
        'uses'       => 'SurvLoop\Controllers\Admin\AdminController@loadPageURL', 
        'middleware' => ['auth']
    ]);
    
    
    Route::get( '/vendor/wikiworldorder/survloop/src/Public/jquery-ui-1.12.1/images/{file}',
        'SurvLoop\\Controllers\\SurvRoutes@catchJqueryUiMappingError');
    

});
