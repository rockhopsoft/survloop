<?php
namespace SurvLoop;

use Illuminate\Support\ServiceProvider;

class SurvLoopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
        $this->publishes([
              __DIR__.'/Views'                       => base_path('resources/views/vendor/survloop'),
              __DIR__.'/Views/auth'                  => base_path('resources/views/auth'),
              __DIR__.'/Views/auth/passwords'        => base_path('resources/views/auth/passwords'),
              __DIR__.'/Uploads'                     => base_path('storage/app/up/survloop'),
              __DIR__.'/Public'                      => base_path('public/survloop'),
              __DIR__.'/Models'                      => base_path('app/Models'),
              __DIR__.'/Models'                      => base_path('app/Models/SurvLoop'),
              __DIR__.'/Database/2018_11_30_000000_create_survloop_tables.php'
                => base_path('database/migrations/2018_11_30_000000_create_survloop_tables.php'),
              __DIR__.'/Database/SurvLoopSeeder.php' => base_path('database/seeds/SurvLoopSeeder.php'),
              __DIR__.'/Database/ZipCodeSeeder.php'  => base_path('database/seeds/ZipCodeSeeder.php'),
        ]);
    }
}