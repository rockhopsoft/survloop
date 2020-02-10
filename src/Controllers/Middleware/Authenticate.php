<?php
/**
  * Authenticate assists with the login redirect process.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$this->redirectIsBad($_SERVER["REQUEST_URI"])) {
            session()->put('loginRedir', $_SERVER["REQUEST_URI"]);
            session()->put('loginRedirTime', time());
            session()->save();
        }
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Check for pitfalls which are probably not the user's intent.
     *
     * @param  string  $redir
     * @return boolean
     */
    protected function redirectIsBad($redir)
    {
        $badRedir = false;
        $bads = [
            '/css/', 
            '/font/',
            '/gen-kml/', 
            '/sys/dyna/',
            '/survloop/uploads/'
        ];
        foreach ($bads as $fold) {
            if (strpos($redir, $fold) !== false) {
                $badRedir = true;
            }
        }
        $bads = [
            '.js', 
            '.css',
            '.kml',
            '.eot'
        ];
        $ext = $redir;
        $pos = strpos($ext, '?');
        if ($pos > 0) {
            $ext = substr($ext, 0, $pos);
        }
        $pos = strrpos($ext, '.');
        if ($pos > 0) {
            $ext = substr($ext, $pos);
        }
        if (in_array($ext, $bads)) {
            $badRedir = true;
        }
        return $badRedir;
    }

}