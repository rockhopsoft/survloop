<?php
/**
  * SurvLoopServiceProvider manages which package files and folders need to be copied to elsewhere in the system.
  * This mostly just runs after installation, and perhaps of some other code updates. 
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop;

use Illuminate\Support\ServiceProvider;

class SurvLoopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
        $this->publishes([
            __DIR__.'/Views'                => base_path('resources/views/vendor/survloop'),
            __DIR__.'/Views/auth'           => base_path('resources/views/auth'),
            __DIR__.'/Views/auth/passwords' => base_path('resources/views/auth/passwords'),
            __DIR__.'/Uploads'              => base_path('storage/app/up/survloop'),
            __DIR__.'/Models'               => base_path('storage/app/models/survloop'),
            __DIR__.'/Models'               => base_path('app/Models'),

            __DIR__.'/Database/2018_11_30_000000_create_survloop_tables.php'
                => base_path('database/migrations/2018_11_30_000000_create_survloop_tables.php'),
            __DIR__.'/Database/SurvLoopSeeder.php' => base_path('database/seeds/SurvLoopSeeder.php'),
            __DIR__.'/Database/ZipCodeSeeder.php'  => base_path('database/seeds/ZipCodeSeeder.php'),
            
            __DIR__.'/Controllers/Middleware/Authenticate.php' => base_path('app/Http/Middleware/Authenticate.php'),
            __DIR__.'/Controllers/Middleware/routes-api.php'   => base_path('routes/api.php'),
            ]);
    }
}