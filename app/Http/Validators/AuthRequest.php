<?php

namespace App\Http\Validators;

use Framework\Http\Validator;

class AuthRequest extends Validator
{
    /**
     * rules
     * 
     * @var array
     */
    protected static $rules = [
        'email' => 'required|max_len,255',
        'password' => 'required|max_len,255'
    ];

    /**
     * custom errors messages
     * 
     * @var array
     */
    protected static $messages = [
        //
    ];
}
