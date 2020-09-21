<?php
/**
  * SurvLoopServiceProvider manages which package files and folders need to be copied to elsewhere in the system.
  * This mostly just runs after installation, and perhaps of some other code updates. 
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.0
  */
namespace SurvLoop;

use SurvLoop\SurvLoopFacade;
use Illuminate\Support\ServiceProvider;

class SurvLoopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('survloopfacade', function($app) {
            return new SurvLoopFacade();
        });
        $this->loadRoutesFrom(__DIR__ . '/Routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'survloop');
        $libDir = '/vendor/rockhopsoft/survloop-libraries/src/';
        $dbMig  = '2020_09_14_000000_create_survloop_tables';
        $this->publishes([
            
            __DIR__ . '/Views'
                => base_path('resources/views/vendor/survloop'),

            __DIR__ . '/Views/auth'
                => base_path('resources/views/auth'),

            __DIR__ . '/Views/auth/passwords' 
                => base_path('resources/views/auth/passwords'),

            __DIR__ . '/Uploads'
                => base_path('storage/app/up/survloop'),

            __DIR__ . '/Models'
                => base_path('app/Models'),

            __DIR__ . '/Controllers/Middleware/routes-api.php' 
                => base_path('routes/api.php'),

            __DIR__ . '/Controllers/Middleware/routes-web.php' 
                => base_path('routes/web.php'),

            __DIR__ . '/Controllers/Middleware/Authenticate.php' 
                => base_path('app/Http/Middleware/Authenticate.php'),

            __DIR__ . '/Database/' . $dbMig . '.php'
                => base_path('database/migrations/' . $dbMig . '.php'),

            __DIR__ . '/Database/SurvLoopSeeder.php'
                => base_path('database/seeders/SurvLoopSeeder.php'),

            base_path($libDir . 'geo/ZipCodeSeeder.php')
                => base_path('database/seeders/ZipCodeSeeder.php'),

            base_path($libDir . 'js/zxcvbn.js')
                => base_path('public/survloop/zxcvbn.js')

        ]);
    }
}