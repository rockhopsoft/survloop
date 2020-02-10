<?php
/**
  * routes-admin.php registers all the paths used in the SurvLoop admin area.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.2.5
  */

Route::get(
    '/css-reload', 
    $path . 'Admin\\AdminController@getCSS'
);

Route::post(
    '/ajadm',
    [
        'uses'       => $path . 'Admin\\AdminController@ajaxChecksAdmin',
        'middleware' => 'auth'
    ]
);
Route::get(
    '/ajadm',
    [
        'uses'       => $path . 'Admin\\AdminController@ajaxChecksAdmin',
        'middleware' => 'auth'
    ]
);
Route::post(
    '/ajadm/{type}', 
    [
        'uses'       => $path . 'Admin\\AdminController@ajaxChecksAdmin',
        'middleware' => 'auth'
    ]
);
Route::get(
    '/ajadm/{type}', 
    [
        'uses'       => $path . 'Admin\\AdminController@ajaxChecksAdmin',
        'middleware' => 'auth'
    ]
);

///////////////////////////////////////////////////////////

Route::get( '/admin', [
    'uses'       => $path . 'Admin\\AdminController@loadPageDashboard', 
    'middleware' => ['auth']
]);

Route::get( '/dash', [
    'uses'       => $path . 'Admin\\AdminController@loadPageDashboard', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard', [
    'uses'       => $path . 'Admin\\AdminController@loadPageDashboard', 
    'middleware' => ['auth']
]);

Route::post('/profile/{uname}', [
    'uses'       => $path . 'SurvLoop@showProfile',
    'middleware' => 'auth'
]);
Route::get( 
    '/profile/{uname}', 
    $path . 'SurvLoop@showProfile'
);
Route::post('/my-profile', [
    'uses'       => $path . 'SurvLoop@showMyProfile',
    'middleware' => 'auth'
]);
Route::get( '/my-profile', [
    'uses'       => $path . 'SurvLoop@showMyProfile',
    'middleware' => 'auth'
]);
Route::post('/change-my-password', [
    'uses'       => $path . 'Auth\\UpdatePasswordController@runUpdate',
    'middleware' => 'auth'
]);
Route::get( '/change-my-password', [
    'uses'       => $path . 'Auth\\UpdatePasswordController@runUpdate',
    'middleware' => 'auth'
]);



///////////////////////////////////////////////////////////

Route::get( '/dashboard/logs', [
    'uses'       => $path . 'Admin\\AdminController@logsOverview', 
    'middleware' => ['auth']
]);
Route::get( '/dashboard/logs/session-stuff', [
    'uses'       => $path . 'Admin\\AdminController@logsSessions', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/systems-check', [
    'uses'       => $path . 'Admin\\AdminController@systemsCheck',    
    'middleware' => ['auth']
]);
Route::post('/dashboard/systems-check', [
    'uses'       => $path . 'Admin\\AdminController@systemsCheck',    
    'middleware' => ['auth']
]);

Route::get( '/dashboard/contact', [
    'uses'       => $path . 'Admin\\AdminEmailController@manageContact', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/emails', [
    'uses'       => $path . 'Admin\\AdminEmailController@manageEmails', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/emails', [
    'uses'       => $path . 'Admin\\AdminEmailController@manageEmails', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/sent-emails', [
    'uses'       => $path . 'Admin\\AdminEmailController@printSentEmails', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/send-email', [
    'uses'       => $path . 'Admin\\AdminEmailController@sendEmailPage', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/send-email', [
    'uses'       => $path . 'Admin\\AdminEmailController@sendEmailPage', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/email/{emailID}', [
    'uses'       => $path . 'Admin\\AdminEmailController@manageEmailsPost', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/email/{emailID}', [
    'uses'       => $path . 'Admin\\AdminEmailController@manageEmailsForm', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/pages/snippets/{blurbID}', [
    'uses' => $path . 'Admin\\AdminController@blurbEditSave', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/pages/snippets/{blurbID}', [
    'uses' => $path . 'Admin\\AdminController@blurbEdit', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/users/email', [
    'uses' => $path . 'Admin\\AdminController@userEmailing', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/users', [
    'uses' => $path . 'Admin\\AdminController@userManage', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/users', [
    'uses' => $path . 'Admin\\AdminController@userManage', 
    'middleware' => ['auth']
]);


///////////////////////////////////////////////////////////


Route::post('/dashboard/settings', [
    'uses'       => $path . 'Admin\\AdminController@sysSettings',
    'middleware' => ['auth']
]);

Route::get( '/dashboard/settings', [
    'uses'       => $path . 'Admin\\AdminController@sysSettings',
    'middleware' => ['auth']
]);

Route::post('/dashboard/settings-raw', [
    'uses'       => $path . 'Admin\\AdminController@sysSettingsRaw',
    'middleware' => ['auth']
]);

Route::get( '/dashboard/settings-raw', [
    'uses'       => $path . 'Admin\\AdminController@sysSettingsRaw',
    'middleware' => ['auth']
]);


Route::post('/dashboard/pages/menus', [
    'uses'       => $path . 'Admin\\AdminController@navMenus', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/pages/menus', [
    'uses'       => $path . 'Admin\\AdminController@navMenus', 
    'middleware' => ['auth']
]);

Route::post('/dashboard/images/gallery', [
    'uses'       => $path . 'Admin\\AdminTreeController@imgGallery', 
    'middleware' => ['auth']
]);

Route::get( '/dashboard/images/gallery', [
    'uses'       => $path . 'Admin\\AdminTreeController@imgGallery', 
    'middleware' => ['auth']
]);


///////////////////////////////////////////////////////////


Route::get( '/dashboard/systems-update', [
    'uses'       => 'SurvLoop\Controllers\SystemUpdate@index', 
    'middleware' => ['auth']
]);
