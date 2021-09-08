<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Framework\System\Auth;
use App\Helpers\Countries;
use App\Mails\WelcomeMail;
use Framework\Http\Request;
use Framework\Routing\View;
use Framework\Routing\Controller;
use App\Database\Repositories\Users;
use App\Http\Validators\AuthRequest;
use App\Mails\EmailConfirmationMail;
use App\Database\Repositories\Tokens;
use App\Http\Validators\RegisterUser;

/**
 * Manage user authentication
 */
class AuthController extends Controller
{    
    /**
     * display login page
     *
     * @return void
     */
    public function login(): void
    {
        if (!Auth::check()) {
            View::render('auth.login');
        }

        Auth::redirect();
    }

    /**
     * display signup page
     *
     * @return void
     */
    public function signup(): void
    {
        if (!Auth::check()) {
            $countries = Countries::all();
            View::render('auth.signup', compact('countries'));
        }

        Auth::redirect();
    }

	/**
	 * authenticate user
	 * 
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Users $users
     * @param  \App\Database\Repositories\Tokens $tokens
	 * @return void
	 */
	public function authenticate(Request $request, Users $users, Tokens $tokens): void
	{
        AuthRequest::validate($request->except('csrf_token'))->redirectOnFail();
        Auth::attempt($request, $users, $tokens);
    }
        
    /**
     * register new user
     *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Users $users
     * @param  \App\Database\Repositories\Tokens $tokens
     * @return void
     */
    public function register(Request $request, Users $users, Tokens $tokens): void
    {
        RegisterUser::register()->validate($request->except('csrf_token'))->redirectOnFail();
        Auth::create($request, $users);

        if (!config('security.auth.email_verification')) {
            WelcomeMail::send($request->email, $request->name);
            redirect()->url('login')->withAlert('success', __('user_registered'))->go();
        }
        
        $token = random_string(50, true);

        if (EmailConfirmationMail::send($request->email, $token)) {
            $tokens->store($request->email, $token, Carbon::now()->addDay()->toDateTimeString());
            redirect()->url('login')->withAlert('success', __('verify_email_link_sent'))->go();
        }
        
        redirect()->back()->withAlert('error', __('verify_email_link_not_sent'))->go();
    }
	
	/**
	 * logout
	 *
     * @param  string $redirect
	 * @return void
	 */
	public function logout(string $redirect = '/'): void
	{
		Auth::forget();
        redirect()->url($redirect)->go();
	}
}
