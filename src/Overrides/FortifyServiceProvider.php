<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use RockHopSoft\Survloop\Controllers\Auth\AuthController;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(function ($request) {
            $auth = new AuthController($request);
            return $auth->printLoginView();
        });

        Fortify::authenticateUsing(function ($request) {
            $auth = new AuthController($request);
            return $auth->loginAuthUsing();
        });

        Fortify::registerView(function ($request) {
            $auth = new AuthController($request);
            return $auth->printRegisterView();
        });

        Fortify::requestPasswordResetLinkView(function ($request) {
            $auth = new AuthController($request);
            return $auth->printPasswordResetLinkView();
        });

        Fortify::resetPasswordView(function ($request) {
            $auth = new AuthController($request);
            return $auth->printPassReset();
        });

        Fortify::confirmPasswordView(function ($request) {
            return view(
                'vendor.survloop.auth.passwords.password-confirm',
                ['request' => $request]
            );
        });

        Fortify::twoFactorChallengeView(function ($request) {
            return view(
                'vendor.survloop.auth.two-factor-challenge',
                ['request' => $request]
            );
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}