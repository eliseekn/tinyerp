<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

use Carbon\Carbon;
use Framework\System\Auth;
use App\Helpers\DateHelper;
use Framework\Http\Request;
use Configula\ConfigFactory;
use Framework\Http\Redirect;
use Framework\Http\Response;
use Framework\Routing\Route;
use Framework\System\Cookies;
use Framework\System\Encryption;
use Framework\System\Session;
use Framework\System\Storage;

/**
 * Cookies management functions
 */

if (!function_exists('create_cookie')) {
	/**
	 * create cookie and set value
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  int $expire in seconds
	 * @param  bool $secure
	 * @param  string $domain
	 * @return bool
	 */
    function create_cookie(string $name, string $value, int $expire = 3600, bool $secure = false, string $domain = ''): bool 
    {
        return Cookies::create($name, $value, $expire, $secure, $domain);
	}
}

if (!function_exists('get_cookie')) {
	/**
	 * return cookie value
	 *
	 * @param  string $name
	 * @return string
	 */
	function get_cookie(string $name): string
	{
        return Cookies::get($name);
	}
}

if (!function_exists('cookie_has')) {
	/**
	 * check if cookie exists
	 *
	 * @param  string $name
	 * @return bool
	 */
	function cookie_has(string $name): bool
	{
		return Cookies::has($name);
	}
}

if (!function_exists('delete_cookie')) {
	/**
	 * delete cookie by name
	 *
	 * @param  string $name
	 * @return bool
	 */
	function delete_cookie(string $name): bool
	{
		return Cookies::delete($name);
	}
}

/**
 * Sessions management functions
 */

if (!function_exists('create_session')) {
	/**
	 * create session data
	 *
	 * @param  string $name
	 * @param  mixed $data
	 * @return void
	 */
	function create_session(string $name, $data): void
	{
		Session::create($name, $data);
	}
}

if (!function_exists('session_get')) {
	/**
	 * get session data
	 *
	 * @param  string $name
	 * @param  mixed $default
	 * @return mixed
	 */
	function session_get(string $name, $default = null)
	{
		return Session::get($name, $default);
	}
}

if (!function_exists('session_pull')) {
	/**
	 * get session data and close it
	 *
	 * @param  string $name
	 * @return mixed
	 */
	function session_pull(string $name)
	{
		return Session::pull($name);
	}
}

if (!function_exists('session_put')) {
	/**
	 * add data to session or create if empty
	 *
	 * @param  string $name
	 * @param  mixed $data
	 * @param  mixed $default
	 * @return mixed
	 */
	function session_put(string $name, $data, $default = null): void
	{
		Session::put($name, $data, $default);
	}
}

if (!function_exists('session_has')) {
	/**
	 * check if session exists
	 *
	 * @param  string $name
	 * @return bool
	 */
	function session_has(string $name): bool
	{
		return Session::has($name);
	}
}

if (!function_exists('session_flush')) {
	/**
	 * flush session
	 *
	 * @param  array $names
	 * @return void
	 */
	function session_flush(array $names): void
	{
		Session::flush(implode(',', $names));
	}
}

if (!function_exists('auth_attempts_exceeded')) {    
    /**
     * check if auth attempts exceeded
     *
     * @return bool
     */
    function auth_attempts_exceeded(): bool
    {
        if (!config('security.auth.max_attempts')) {
            return false;
        }

        $unlock_timeout = session_get('auth_attempts_timeout');
        $attempts = session_get('auth_attempts');

        if (is_null($attempts) || is_null($unlock_timeout)) {
            return false;
        }

        return ($attempts > config('security.auth.max_attempts')) && Carbon::now()->lte($unlock_timeout);
    }
}

if (!function_exists('auth')) {
	/**
	 * get authenticated user session data
	 *
     * @param  string $key
	 * @return mixed
	 */
	function auth(string $key)
	{
        $user = session_get('user');

        if (is_null($user)) {
            return false;
        }

		return $user->{$key};
	}
}

/**
 * Security utils functions
 */

if (!function_exists('pwd_hash')) {    
    /**
     * password hash helper
     *
     * @param  string $password
     * @return string
     */
    function pwd_hash(string $password): string
    {
        return Encryption::hash($password);
    }
}

if (!function_exists('escape')) {
	/**
     * escape html and others scripting languages
     *
     * @param  string $str
     * @return string
     */
    function escape(string $str): string
    {
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        $str = strip_tags($str);
        return $str;
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * generate crsf token
     *
     * @return string
     */
    function generate_csrf_token(): string
    {
        if (session_has('csrf_token')) {
            $csrf_token = session_get('csrf_token');
        } else {
            $csrf_token = bin2hex(random_bytes(32));
            create_session('csrf_token', $csrf_token);
        }

        return $csrf_token;
    }
}

if (!function_exists('csrf_token_input')) {
    /**
     * generate crsf token html input
     *
     * @return string
     */
    function csrf_token_input(): string
    {
        return '<input type="hidden" name="csrf_token" id="csrf_token" value="' . generate_csrf_token() . '">';
    }
}

if (!function_exists('csrf_token_meta')) {
    /**
     * generate crsf token html meta
     *
     * @return string
     */
    function csrf_token_meta(): string
    {
        return '<meta name="csrf_token" content="' . generate_csrf_token() . '">';
    }
}

if (!function_exists('method_input')) {
    /**
     * generate method input
     *
     * @param  string $method
     * @return string
     */
    function method_input(string $method): string
    {
        return '<input type="hidden" name="request_method" value="' . $method . '">';
    }
}

if (!function_exists('valid_csrf_token')) {
    /**
     * check if crsf token is valid
     *
     * @param  string $csrf_token
     * @return bool
     */
    function valid_csrf_token(string $csrf_token): bool
    {
        return hash_equals(session_get('csrf_token'), $csrf_token);
    }
}

/**
 * Miscellaneous URL utils functions
 */

if (!function_exists('url')) {
	/**
	 * generate abosulte url
	 *
	 * @param  string $uri
	 * @return string
	 */
	function url(string $uri, $params = null): string
	{
        $url = config('app.url') . ltrim($uri, '/');
        $params = is_array($params) ? (empty($params) ? '' : implode('/', $params)) : $params;

		return is_null($params) ? $url : $url . '/' . $params;
	}
}

if (!function_exists('route_uri')) {    
    /**
     * get route uri
     *
     * @param  string $name
     * @param  mixed $params
     * @return string
     */
    function route_uri(string $route, $params = null): string
    {
        if (!isset(Route::$names[$route])) {
            throw new Exception('Route name "' . $route . '" is not defined.');
        }

        $uri = Route::$names[$route];
        $pieces = explode('/', $uri);

        if (is_null($params)) {
            foreach ($pieces as $piece) {
                if (strpos($piece, '+)?')) {
                    $uri = substr_replace($uri, '', strpos($uri, $piece), strlen($piece));
                    continue;
                }
            }
        }

        else {
            $params = is_array($params) ? $params : [$params];
            reset($params);

            foreach ($pieces as $piece) {
                if (strpos($piece, '+)')) {
                    if (strpos($piece, '+)?')) {
                        if (!isset($params)) {
                            $uri = substr_replace($uri, '', strpos($uri, $piece), strlen($piece));
                            next($params);
                            continue;
                        }
                    }
                        
                    $uri = substr_replace($uri, current($params), strpos($uri, $piece), strlen($piece));
                    next($params);
               }
            }
        }

        $uri = str_replace('//', '/', $uri);
        return $uri;
    }
}

if (!function_exists('route')) {    
    /**
     * get route absolute url
     *
     * @param  string $name
     * @param  mixed $params
     * @return string
     */
    function route(string $route, $params = null): string
    {
        return url(route_uri($route, $params));
    }
}

if (!function_exists('assets')) {    
    /**
     * generate assets url from public folder
     *
     * @param  string $asset
     * @param  mixed $params
     * @return string
     */
    function assets(string $asset, $params = null): string
    {
        return url('public/' . $asset, $params);
    }
}

if (!function_exists('storage')) {    
    /**
     * generate storage url
     *
     * @param  string $path
     * @param  mixed $params
     * @return string
     */
    function storage(string $path, $params = null): string
    {
        return url('storage/' . $path, $params);
    }
}

if (!function_exists('resources')) {    
    /**
     * generate resources url
     *
     * @param  string $path
     * @param  mixed $params
     * @return string
     */
    function resources(string $path, $params = null): string
    {
        return url('resources/' . $path, $params);
    }
}

if (!function_exists('current_url')) {
	/**
	 * get current url
	 *
	 * @return string
	 */
	function current_url(): string
	{
		return url((new Request())->fullUri());
	}
}

if (!function_exists('in_uri')) {	
	/**
	 * check if current url contains specific string
	 *
	 * @param  string $str
	 * @return bool
	 */
	function in_uri(string $str): bool
	{
        return preg_match('/' . $str . '/', explode('//', current_url())[1]);
	}
}

if (!function_exists('response')) {
    /**
     * Response helper function
     *
     * @return \Framework\Http\Response
     */
    function response(): \Framework\Http\Response
    {
        return new Response();
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect helper function
     *
     * @return \Framework\Http\Redirect
     */
    function redirect(): \Framework\Http\Redirect
    {
        return new Redirect();
    }
}

/**
 * Miscellaneous utils functions
 */

if (!function_exists('date_helper')) {    
    /**
     * datehelper helper function
     *
     * @param  mixed $date
     * @param  string $format
     * @return string
     */
    function date_helper($date = null, string $format = 'human'): string
    {
        $dh = DateHelper::format($date);

        if ($format === 'human') {
            return $dh->human();
        } else if ($format === 'timestamp') {
            return $dh->timestamp();
        } else {
            return $dh->datetime($format);
        }
    }
}

if (!function_exists('real_path')) {    
    /**
     * replace '.' by DIRECTORY_SEPARATOR
     *
     * @param  string $path
     * @return string
     */
    function real_path(string $path): string
    {
        return str_replace('.', DIRECTORY_SEPARATOR, $path);
    }
}

if (!function_exists('absolute_path')) {    
    /**
     * get absolute path
     *
     * @param  string $path
     * @return string
     */
    function absolute_path(string $path): string
    {
        return APP_ROOT . real_path($path) . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('slugify')) {
	/**
	 * generate slug from string with utf8 encoding
	 *
	 * @param  string $str
	 * @param  string $separator
	 * @return string
	 * @link   https://ourcodeworld.com/articles/read/253/creating-url-slugs-properly-in-php-including-transliteration-support-for-utf-8
	 */
	function slugify(string $str, string $separator = '-'): string
	{
		return strtolower(
			trim(
				preg_replace(
					'~[^0-9a-z]+~i',
					$separator,
					html_entity_decode(
						preg_replace(
							'~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
							'$1',
							htmlentities($str, ENT_QUOTES, 'UTF-8')
						),
						ENT_QUOTES,
						'UTF-8'
					)
				),
				$separator
			)
		);
	}
}

if (!function_exists('truncate')) {
	/**
	 * truncate string
	 *
	 * @param  string $str
	 * @param  int $length
	 * @param  string $end
	 * @return string
	 */
	function truncate(string $str, int $length, string $end = '[...]'): string
	{
		return mb_strimwidth($str, 0, $length, $end);
	}
}

if (!function_exists('random_string')) {
	/**
	 * random string generator
	 *
	 * @param  int $length
	 * @param  bool $alphanumeric
	 * @return string
	 * @link   https://www.php.net/manual/en/function.str-shuffle.php
	 */
	function random_string(int $length = 10, bool $alphanumeric = false): string
	{
		$chars = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$chars .= $alphanumeric ? '0123456789' : '';
		return substr(str_shuffle($chars), 0, $length);
	}
}

if (!function_exists('config')) {	
	/**
	 * read configuration
	 *
	 * @param  string $data
	 * @return mixed
	 */
	function config(string $data)
	{
        $file = substr($data, 0, strpos($data, '.'));
        $path = substr($data, strpos($data, '.') + 1, strlen($data));
        $config = ConfigFactory::loadPath(absolute_path('config') . $file . '.php');
        return $config->get($path);
	}
}

if (!function_exists('get_file_extension')) {	
	/**
	 * get file extension
	 *
	 * @param  string $file
	 * @return string
	 */
	function get_file_extension(string $file): string
	{
		if (empty($file) || strpos($file, '.') === false) {
            return '';
		}
		
		$file_ext = explode('.', $file);
		return $file_ext === false ? '' : end($file_ext);
	}
}

if (!function_exists('get_file_name')) {	
	/**
	 * get file name
	 *
	 * @param  string $file
	 * @return string
	 */
	function get_file_name(string $file): string
	{
		if (empty($file) || strpos($file, '.') === false) {
            return '';
		}
		
		$filename = explode('.', $file);
		return $filename === false ? '' : $filename[0];
	}
}

if (!function_exists('time_elapsed')) {	
	/**
	 * get time elapsed
	 *
	 * @param  mixed $datetime
	 * @param  int $level
	 * @return string
	 * @link   https://stackoverflow.com/a/18602474
	 */
	function time_elapsed($datetime, int $level = 1): string
	{
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => __('year'),
			'm' => __('month'),
			'w' => __('week'),
			'd' => __('day'),
			'h' => __('hour'),
			'i' => __('minute'),
			's' => __('second'),
		);

		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . (($diff->$k > 1 && $v[-1] !== 's')  ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		$string = array_slice($string, 0, $level);
		return $string ? implode(', ', $string) . __('ago') : __('now');
	}
}

if (!function_exists('__')) {    
    /**
     * return translated word or expression
     *
     * @param  string $expr
     * @return string
     */
    function __(string $expr): string
    {
        $lang = Auth::check() ? auth('lang') : config('app.lang');
        $config = ConfigFactory::loadPath(absolute_path('resources.lang') . $lang . '.php');
		return $config($expr, '');
    }
}

if (!function_exists('generate_pagination')) {
	/**
	 * pagination parameters generator
	 *
	 * @param  int $page
	 * @param  int $total_items
	 * @param  int $items_per_pages
	 * @return array
	 */
	function generate_pagination(int $page, int $total_items, int $items_per_pages): array
	{
		//get total number of pages
		$total_pages = $items_per_pages > 0 ? ceil($total_items / $items_per_pages) : 1;

		//get first item of page (offset)
		$first_item = ($page - 1) * $items_per_pages;

		//return paramaters
		return [
			'first_item' => $first_item,
			'total_items' => $total_items,
			'items_per_pages' => $items_per_pages,
			'page' => $page,
			'total_pages' => $total_pages
		];
	}
}

if (!function_exists('save_log')) {
    /**
	 * save log message to file
	 *
	 * @param  string $message 
	 * @return void
	 */
	function save_log(string $message): void
	{
        if (!Storage::path(config('storage.logs'))->isDir()) {
            Storage::path(config('storage.logs'))->createDir();
        }

        error_log($message);
    }
}

/**
 * Curl HTTP requests
 */

if (!function_exists('curl')) {
    /**
     * send asynchronous HTTP request using curl
     *
     * @param  string $method
     * @param  array $urls
     * @param  array $data
     * @param  bool $json
     * @return array
     * @link   https://niraeth.com/php-quick-function-for-asynchronous-multi-curl/
     *         https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request
     *         https://www.codexworld.com/post-receive-json-data-using-php-curl/
     *         https://stackoverflow.com/questions/13420952/php-curl-delete-request
     */
    function curl(string $method, array $urls, array $headers = [], ?array $data = null, bool $json = false): array 
    {
        $response_headers = [];
        $response = [];
        $curl_array = [];
        $curl_multi = curl_multi_init(); //init multiple curl processing

        foreach ($urls as $key => $url) {
            $curl_array[$key] = curl_init();
            $curl = $curl_array[$key];

            //set options
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 300000);

            //set method
            if (strtoupper($method) !== 'GET') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            }
            
            //set data
            if (!is_null($data)) {
                if ($json) {
                    $data = json_encode($data);
                    $headers[] = 'Content-Type: application/json';
                }
        
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }

            //set headers
            if (!empty($headers)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            }
            
            //retrieves response headers 
            curl_setopt(
                $curl,
                CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$response_headers, $key) {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) return $len;

                    $response_headers[$key][strtolower(trim($header[0]))][] = trim($header[1]);
                    return $len;
                }
            );

            curl_multi_add_handle($curl_multi, $curl);
        }

        $i = null;

        do {
            curl_multi_exec($curl_multi, $i);
        } while ($i);

        //retrieves response
        foreach ($curl_array as $key => $curl) {
            $response[$key] = curl_multi_getcontent($curl);
            curl_multi_remove_handle($curl_multi, $curl);
        }

        curl_multi_close($curl_multi);

        //return response
        return [
            'headers' => $response_headers,
            'body' => $response
        ];
    }
}

/**
 * .env file management functions
 */

if (!function_exists('env')) {    
    /**
     * retrieves environnement key variable
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $data = getenv($key, true);
        return $data === false || empty($data) ? $default : $data;
    }
}

if (!function_exists('set_env')) {    
    /**
     * set environnement variables
     *
     * @param  array $config
     * @return void
     */
    function set_env(array $config): void
    {
        if (!is_array($config) || empty($config)) {
            return;
        }

        foreach ($config as $key => $value) {
            putenv("$key=$value");
        } 
    }
}

if (!function_exists('save_env')) {    
    /**
     * save environnement variables to file
     *
     * @param  array $config
     * @return void
     */
    function save_env(array $config): void
    {
        set_env($config);

        $data = '';

        foreach ($config as $key => $value) {
            $data .= "$key=$value";

            if ($key === 'APP_CURRENCY') {
                $data .= PHP_EOL;
            }

            if ($key === 'DB_PASSWORD') {
                $data .= PHP_EOL;
            }
        }

        Storage::path()->writeFile('.env', $data);
    }
}

if (!function_exists('load_env')) {    
    /**
     * load environnement variables
     *
     * @return void
     */
    function load_env(): void
    {
        if (!Storage::path()->isFile('.env')) {
            return;
        }

        $lines = file(Storage::path()->file('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            set_env([trim($name) => trim($value)]);
        }
    }
}
