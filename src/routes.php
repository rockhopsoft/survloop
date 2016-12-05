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
        
    Route::post('/',                       'SurvLoop\\Controllers\\SurvLoop@index');
    Route::get( '/',                       'SurvLoop\\Controllers\\SurvLoop@index');
    Route::post('/sub',                    'SurvLoop\\Controllers\\SurvLoop@index');
    
    Route::get( '/ajax',                   'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::get( '/holdSess',               'SurvLoop\\Controllers\\SurvLoop@holdSess');
    Route::get( '/test',                   'SurvLoop\\Controllers\\SurvLoop@testRun');
    Route::get( '/restart',                'SurvLoop\\Controllers\\SurvLoop@restartSess');
    Route::get( '/sessDump',               'SurvLoop\\Controllers\\SurvLoop@sessDump');
    
    Route::post('/u/{nodeSlug}',           'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    Route::get( '/u/{nodeSlug}',           'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    
    Route::get( '/report/{cid}/{ComSlug}', 'SurvLoop\\Controllers\\SurvLoop@byID');
    Route::get( '/report/{cid}/',          'SurvLoop\\Controllers\\SurvLoop@byID');
    Route::get( '/report/{cid}',           'SurvLoop\\Controllers\\SurvLoop@byID');
    
    Route::get( '/xml/report/{cid}/',      'SurvLoop\\Controllers\\SurvLoop@xmlByID');
    
    Route::get( '/xml-example',            'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/xml-example.xml',        'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/xml-schema',             'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/xml-schema.xsd',         'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    
    Route::get( '/up/{cid}/{upID}',        'SurvLoop\\Controllers\\SurvLoop@retrieveUpload');
    
    
    Route::get( '/fresh/creator',          'SurvLoop\\Controllers\\AdminTreeController@freshUser');
    Route::post('/fresh/database',         'SurvLoop\\Controllers\\AdminTreeController@freshDB');
    Route::get( '/fresh/database',         'SurvLoop\\Controllers\\AdminTreeController@freshDB');
    Route::post('/fresh/user-experience',  'SurvLoop\\Controllers\\AdminTreeController@freshUX');
    Route::get( '/fresh/user-experience',  'SurvLoop\\Controllers\\AdminTreeController@freshUX');
    
    
    /********************************************************/
    
    Route::post('/register',               'SurvLoop\Controllers\Auth\SurvRegisterController@register');
    Route::post('/afterLogin',             'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/afterLogin',             'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/logout',                 'SurvLoop\Controllers\Auth\AuthController@getLogout');
    
    /*
    // Authentication routes...
    Route::post('/login',                  'SurvLoop\Controllers\Auth\AuthController@postLogin');
    Route::get( '/login',                  'SurvLoop\Controllers\Auth\AuthController@getLogin');
    
    // Registration routes...
    Route::get( '/register',               'SurvLoop\Controllers\Auth\AuthController@getRegister');
    
    // Password reset link request routes...
    Route::post('/password/email',         'SurvLoop\Controllers\Auth\PasswordController@postEmail');
    Route::get( '/password/email',         'SurvLoop\Controllers\Auth\PasswordController@getEmail');
    
    // Password reset routes...
    Route::post('/password/reset',         'SurvLoop\Controllers\Auth\PasswordController@postReset');
    Route::get( '/password/reset/{token}', 'SurvLoop\Controllers\Auth\PasswordController@getReset');
    */
    
    
    /********************************************************/
    
    
    Route::get('/admin', [
        'uses' => 'SurvLoop\\Controllers\\SurvLoop@dashboardDefault', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard', [
        'uses' => 'SurvLoop\\Controllers\\SurvLoop@dashboardDefault', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/home', [
        'uses' => 'SurvLoop\\Controllers\\SurvLoop@dashboardDefault', 
        'middleware' => ['auth']
    ]);
    
    
    /********************************************************/
    
    
    Route::post('/profile/{uid}',     [
        'uses' => 'SurvLoop\Controllers\SurvLoop@updateProfile',     
        'middleware' => 'auth'
    ]);
    
    Route::get( '/profile/{uid}',     [
        'uses' => 'SurvLoop\Controllers\SurvLoop@showProfile',             
        'middleware' => 'auth'
    ]);
    
    Route::post('/dashboard/user/{uid}',     [
        'uses' => 'SurvLoop\Controllers\SurvLoop@updateProfile',     
        'middleware' => 'auth'
    ]);
    
    Route::get( '/dashboard/user/{uid}',     [
        'uses' => 'SurvLoop\Controllers\SurvLoop@showProfile',             
        'middleware' => 'auth'
    ]);
    
    
    
    
    
    Route::get('/dashboard/subs', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/all', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/incomplete', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@listSubsIncomplete',    
        'middleware' => ['auth']
    ]);
    
    
    Route::post('/dashboard/subs/emails', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/emails', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/subs/email/{emailID}', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@manageEmailsPost', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/email/{emailID}', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@manageEmailsForm', 
        'middleware' => ['auth']
    ]);
    
    
    
    /********************************************************/
    
    
    Route::get('/dashboard/tree', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@treeSessions',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/stats', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@treeStats', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/map', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/map', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/data', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/data', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/conds', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/conds', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/map/node/{nID}', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/map/node/{nID}', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/xmlmap', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/xmlmap', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/xmlmap/node/{nodeIN}', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/xmlmap/node/{nodeIN}', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/workflows', [
        'uses' => 'SurvLoop\Controllers\AdminTreeController@workflows', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/switch/{treeID}', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@switchTreeAdmin', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/tree/switch', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@switchTreeAdmin', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/tree/new', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@newTree', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/tree/new', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@newTree', 
        'middleware'     => ['auth']
    ]);
    
    
    
    
    /********************************************************/
    
    
    
    Route::get('/dashboard/db', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@index', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/all', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@full', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/field-matrix', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldMatrix', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/addTable', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@addTable', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/addTable', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@addTable', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/sortTable', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@tblSort', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/table/{tblName}/edit', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@editTable', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}/edit', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@editTable', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}/sort', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fldSort', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@viewTable', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@editField', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@editField', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@addTableFld', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/field/{tblAbbr}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@addTableFld', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/ajax-field/{FldID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldAjax', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldDescs', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/all', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldDescsAll', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/{view}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldDescs', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/{view}/all', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldDescsAll', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/fieldDescs/save', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldDescsSave',    
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/ajax/tblFldSelT/{rT}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@tblSelector'
    ]);
    
    Route::get('/dashboard/db/ajax/tblFldSelF/{rF}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fldSelector'
    ]);
    
    Route::get('/dashboard/db/ajax/getSetFlds/{rSet}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@getSetFlds'
    ]);
    
    Route::get('/dashboard/db/ajax/getSetFldVals/{FldID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@getSetFldVals'
    ]);
    
    Route::post('/dashboard/db/fieldXML/save', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldXMLsave', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldXML', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@fieldXML', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@definitions', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/add/{subset}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/add', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/edit/{defID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defEdit', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub/{subset}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/edit-sub/{defID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defEdit', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/sort/{subset}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@defSort', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@businessRules', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/add', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@ruleAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules/add', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@ruleAdd', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/edit/{ruleID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@ruleEdit', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules/edit/{ruleID}',    [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@ruleEdit', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/diagrams', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@diagrams', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/network-map', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@networkMap', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/export', [
        'uses'             => 'SurvLoop\Controllers\DatabaseInstaller@export', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/export/laravel', [
        'uses'             => 'SurvLoop\Controllers\DatabaseInstaller@printExportLaravel', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/install', [
        'uses'             => 'SurvLoop\Controllers\DatabaseInstaller@autoInstallDatabase', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/install', [
        'uses'             => 'SurvLoop\Controllers\DatabaseInstaller@autoInstallDatabase', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/switch/{dbID}', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@switchDB', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/switch', [
        'uses'             => 'SurvLoop\Controllers\AdminDBController@switchDB', 
        'middleware'     => ['auth']
    ]);
    
    Route::post('/dashboard/db/new', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@newDB', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/dashboard/db/new', [
        'uses'             => 'SurvLoop\Controllers\AdminTreeController@newDB', 
        'middleware'     => ['auth']
    ]);
    
    Route::get('/css-reload',         'SurvLoop\\Controllers\\AdminController@getCSS');

});    
