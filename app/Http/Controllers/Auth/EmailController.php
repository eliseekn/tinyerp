<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Mails\WelcomeMail;
use Framework\Http\Request;
use Framework\System\Auth;
use Framework\System\Session;
use Framework\Routing\Controller;
use App\Database\Repositories\Users;
use App\Database\Repositories\Tokens;

/**
 * Manage email confirmation
 */
class EmailController extends Controller
{
	/**
	 * confirm email confirmation link
	 *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Users $users
	 * @return void
	 */
	public function confirm(Request $request, Users $users): void
	{
        $user = $users->findOneByEmail($request->email);

		if (!$user) {
            redirect()->url('signup')->withAlert('error', __('user_not_registered'))->go();
        }

        $users->updateBy(['email', $user->email], ['email_verified' => 1]);
        WelcomeMail::send($user->email, $user->name);
        redirect()->url('login')->withAlert('success', __('user_activated'))->go();
    }
        
    /**
     * authenticate user from email link
     *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Users $users
     * @param  \App\Database\Repositories\Tokens $tokens
     * @return void
     */
    public function authenticate(Request $request, Users $users, Tokens $tokens): void
    {
        $auth_token = $tokens->findOneByEmail($request->email);

        if (!$auth_token || $auth_token->token !== $request->token) {
			response()->send(__('invalid_two_steps_link'));
		}

		if ($auth_token->expire < Carbon::now()->toDateTimeString()) {
			response()->send(__('expired_two_steps_link'));
		}

        $tokens->flush($auth_token->token);
        $user = $users->findOneByEmail($request->email);

        Session::create('user', $user);
        Auth::redirect();
    }
}
