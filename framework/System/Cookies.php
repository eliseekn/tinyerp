<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\System;

/**
 * Manage cookies
 */
class Cookies
{    
    /**
     * create cookie
     *
     * @param  string $name
     * @param  string $value
     * @param  int $expire in seconds
     * @param  bool $secure
     * @param  string $domain
     * @return bool
     */
    public static function create(string $name, string $value, int $expire = 3600, bool $secure = false, string $domain = ''): bool 
    {
        $value = config('security.encryption.cookies') ? Encryption::encrypt($value) : $value;
		return setcookie(config('app.name') . '_' . $name, $value, time() + $expire, '/', $domain, $secure, true);
    }
    
    /**
     * get cookie data
     *
     * @param  string $name
     * @return string
     */
    public static function get(string $name): string
    {
        $value = $_COOKIE[config('app.name') . '_' . $name] ?? '';
		return config('security.encryption.cookies') ? Encryption::decrypt($value) : $value;
    }
    
    /**
     * check if cookie exists
     *
     * @param  string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[config('app.name') . '_' . $name]);
    }
    
    /**
     * delete cookie
     *
     * @param  string $name
     * @return bool
     */
    public static function delete(string $name): bool
    {
        return setcookie(config('app.name') . '_' . $name, '', time() - 3600, '/');
    }
}
