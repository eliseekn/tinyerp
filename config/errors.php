<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

/**
 * Errors configuration
 */

$config = [
    'display' => true,
    'log' => true,

    'views' => [
        '403' => 'errors' . DIRECTORY_SEPARATOR . '403',
        '404' => 'errors' . DIRECTORY_SEPARATOR . '404',
        '500' => 'errors' . DIRECTORY_SEPARATOR . '500'
    ]
];
