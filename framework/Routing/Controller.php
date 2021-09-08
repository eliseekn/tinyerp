<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\Routing;

/**
 * Main controller class
 */
class Controller
{
    /**
     * render view template
     *
     * @param  string $view
     * @param  array $data
     * @param  int $code
     * @return void
     */
    public function render(string $view, array $data = [], int $code = 200): void
    {
        View::render($view, $data, $code);
    }
}
