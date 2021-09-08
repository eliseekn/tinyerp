<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework;

use Exception;
use Framework\Http\Request;
use Framework\Routing\View;
use Framework\Routing\Route;
use Framework\Routing\Router;
use Framework\System\Storage;

/**
 * Main application
 */
class Application
{
    /**
     * load routes files
     *
     * @return void
     */
    public function __construct()
    {
        $routes = Storage::path(config('storage.routes'))->getFiles();

        foreach ($routes as $route) {
            require_once config('storage.routes') . $route;
        }
    }
    
    /**
     * start application
     *
     * @return void
     */
    public function run(): void
    {
        $request = new Request();

        try {
            Router::dispatch($request, Route::$routes);
        } catch (Exception $e) {
            //log exception message
            if (config('errors.log') === true) {
                save_log('Application Error: ' . $e);
            }

            //send 500 response
            if (config('errors.display') === true) {
                die($e);
            } else {
                if (!empty(config('errors.views.500'))) {
                    View::render(config('errors.views.500'), [], 500);
                } else {
                    response()->send('Try to refresh the page or feel free to contact us if the problem persists', [], 500);
                }
            }
        }
    }
}
