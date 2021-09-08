<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\Console;

use Framework\System\Storage;

/**
 * Create files from templates
 */
class Make
{
    /**
     * get stubs path
     *
     * @return \Framework\Support\Storage
     */
    private static function stubs(): Storage
    {
        return Storage::path(config('storage.stubs'));
    }
    
    /**
     * generate classname
     *
     * @param  string $base_name
     * @param  string $class
     * @param  bool $singular
     * @return array
     */
    public static function generateClass(string $base_name, string $class, bool $singular = false): array
    {
        $base_name = strtolower($base_name);

        if (!$singular) {
            if ($base_name[-1] === 'y') {
                $base_name = rtrim($base_name, 'y');
                $base_name .= 'ies';
            }
        
            if ($base_name[-1] !== 's') {
                $base_name .= 's';
            }
        }

        $name = ucfirst($base_name);

        if (strpos($base_name, '_')) {
            list($f, $s) = explode('_', $base_name);
            $name = ucfirst($f) . ucfirst($s);
        }

        if ($class === 'migration') {
            $class = 'table';
        }

        $class = $name . ucfirst($class);

        if (strpos(strtolower($class), 'table')) {
            $class .= date('_YmdHis');
        }

        return [$base_name, $class];
    }
    
    /**
     * generateResource
     *
     * @param  string $resource
     * @return array
     */
    public static function generateResource(string $resource): array
    {
        if ($resource[-1] === 'y') {
            $resource = rtrim($resource, 'y');
            $resource .= 'ies';
        }

        if ($resource[-1] !== 's') {
            $resource .= 's';
        }

        $resource_folder = 'admin.' . $resource;
        return [$resource, $resource_folder];
    }
    
    /**
     * create routes for resources
     *
     * @param  string $controller
     * @return bool
     */
    public static function createRoute(string $controller): bool
    {
        list($name, $class) = self::generateClass($controller, 'controller');

        $data = self::stubs()->readFile('Route.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('RESOURCENAME', $name, $data);

        if (!Storage::path(config('storage.routes'))->writeFile('admin.php', $data, true)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * create controller file
     *
     * @param  string $controller
     * @param  string|null $namespace
     * @return bool
     */
    public static function createController(string $controller, ?string $namespace = null): bool
    {
        list($name, $class) = self::generateClass($controller, 'controller');

        $data = self::stubs()->readFile('Controller.stub');
        
        $data = is_null($namespace) 
            ? str_replace('NAMESPACE', 'App\Http\Controllers', $data) 
            : str_replace('NAMESPACE', 'App\Http\Controllers\\' . ucfirst($namespace), $data);
        
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('RESOURCENAME', $name, $data);
        $data = str_replace('RESOURCEMODEL', ucfirst($name) . 'Model', $data);

        $path = Storage::path(config('storage.controllers'));

        if (!is_null($namespace)) {
            $path->add(ucfirst($namespace));
        }

        if (!$path->writeFile($class . '.php', $data)) {
            return false;
        }
        
        return true;
    }

    /**
     * create repository file
     *
     * @param  string $repository
     * @return bool
     */
    public static function createRepository(string $repository): bool
    {
        list($name, $class) = self::generateClass($repository, '');

        $data = self::stubs()->readFile('Repository.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('TABLENAME', $name, $data);

        if (!Storage::path(config('storage.repositories'))->writeFile($class . '.php', $data)) {
            return false;
        }
        
        return true;
    }
 
    /**
     * create migration file
     *
     * @param  string $migration
     * @return bool
     */
    public static function createMigration(string $migration): bool
    {
        list($name, $class) = self::generateClass($migration, 'migration');

        $data = self::stubs()->readFile('Migration.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('TABLENAME', $name, $data);

        if (!Storage::path(config('storage.migrations'))->writeFile($class . '.php', $data)) {
            return false;
        }
        
        return true;
    }

    /**
     * create seed file
     *
     * @param  string $seed
     * @return bool
     */
    public static function createSeed(string $seed): bool
    {
        list($name, $class) = self::generateClass($seed, 'seed');

        $data = self::stubs()->readFile('Seed.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('TABLENAME', $name, $data);

        if (!Storage::path(config('storage.seeds'))->writeFile($class . '.php', $data)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * create request validator file
     *
     * @param  string $validator
     * @return bool
     */
    public static function createValidator(string $validator): bool
    {
        list($name, $class) = self::generateClass($validator, '', true);

        $data = self::stubs()->readFile('Validator.stub');
        $data = str_replace('CLASSNAME', $class, $data);

        if (!Storage::path(config('storage.validators'))->writeFile($class . '.php', $data)) {
            return false;
        }

        return true;
    }
    
    /**
     * create middleware file
     *
     * @param  string $middleware
     * @return bool
     */
    public static function createMiddleware(string $middleware): bool
    {
        list($name, $class) = self::generateClass($middleware, '', true);
        
        $data = self::stubs()->readFile('Middleware.stub');
        $data = str_replace('CLASSNAME', $class, $data);

        if (!Storage::path(config('storage.middlewares'))->writeFile($class . '.php', $data)) {
            return false;
        }

        return true;
    }
    
    /**
     * create resources views file
     *
     * @param  string $resource
     * @return bool
     */
    public static function createViews(string $resource): bool
    {
        list($resource_name, $resource_folder) = self::generateResource($resource);

        foreach(self::stubs()->add('views.resources')->getFiles() as $file) {
            $data = self::stubs()->add('views.resources')->readFile($file);
            $data = str_replace('RESOURCENAME', $resource_name, $data);
            $file = str_replace('stub', 'html.twig', $file);

            if (!Storage::path(config('storage.views'))->add($resource_folder)->writeFile($file, $data)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * create mail resources
     *
     * @param  string $mail
     * @return bool
     */
    public static function createMail(string $mail): bool
    {
        list($name, $class) = self::generateClass($mail, 'mail');

        $data = self::stubs()->readFile('Mail.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('RESOURCENAME', $name, $data);

        if (!Storage::path(config('storage.mails'))->writeFile($class . '.php', $data)) {
            return false;
        }

        $data = self::stubs()->add('views')->readFile('email.stub');

        if (!Storage::path(config('storage.views'))->add('emails')->writeFile($name . '.html.twig', $data)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * create view and or layout
     *
     * @param  string|null $view
     * @param  string|null $layout
     * @return bool
     */
    public static function createView(?string $view = null, ?string $layout = null): bool
    {
        $data = is_null($view) && !is_null($layout)
            ? self::stubs()->add('views')->readFile('layout.stub')
            : self::stubs()->add('views')->readFile('blank.stub');

        $data = str_replace('LAYOUTNAME', '{% extends "layouts/' . $layout . '.html.twig" %}', $data);
        $data = is_null($view) ? $data : str_replace('RESOURCENAME', $view, $data);
        
        $path = Storage::path(config('storage.views'));

        if (is_null($view) && !is_null($layout)) {
            $path->add('layouts');
        } else {
            $path->add('admin');
        }
        
        $view = is_null($view) && !is_null($layout) ? $layout : $view;

        if (!$path->writeFile($view . '.html.twig', $data)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * create console command
     *
     * @param  string $name
     * @param  string $description
     * @return bool
     */
    public static function createCommand(string $cmd, string $name, string $description): bool
    {
        list($_name, $class) = self::generateClass($cmd, '', true);

        $data = self::stubs()->readFile('Command.stub');
        $data = str_replace('CLASSNAME', $class, $data);
        $data = str_replace('COMMANDNAME', $name, $data);
        $data = str_replace('COMMANDDESCPTION', $description, $data);

        if (!Storage::path(config('storage.commands'))->writeFile($class . '.php', $data)) {
            return false;
        }

        return true;
    }
}
