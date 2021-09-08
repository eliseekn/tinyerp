<?php

namespace App\Http\Validators;

use GUMP;
use Framework\Http\Validator;
use Framework\Database\Repository;

class RegisterUser extends Validator
{
    /**
     * rules
     * 
     * @var array
     */
    protected static $rules = [
        'name' => 'required|max_len,255',
        'email' => 'required|valid_email|max_len,255|unique,users',
        'phone' => 'required|numeric|max_len,255|unique,users',
        'password' => 'required|max_len,255',
        'company' => 'max_len,255',
        'address' => 'max_len,255'
    ];

    /**
     * custom errors messages
     * 
     * @var array
     */
    protected static $messages = [
        //
    ];
    
    /**
     * register customs validators
     *
     * @return mixed
     */
    public static function register(): self
    {
        GUMP::add_validator('unique', function($field, array $input, array $params, $value) {
            $data = (new Repository($params[0]))->findBy($field, $value);
            return !$data->exists();
        }, "Record of {field} field already exists");

        return new self();
    }
}
