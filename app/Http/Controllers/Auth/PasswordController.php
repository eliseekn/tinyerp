<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Mails\TokenMail;
use Framework\Http\Request;
use Framework\System\Encryption;
use Framework\Routing\Controller;
use App\Database\Repositories\Users;
use App\Http\Validators\AuthRequest;
use App\Database\Repositories\Tokens;

/**
 * Manage password reset
 */
class PasswordController extends Controller
{
	/**
	 * send reset password link notification
	 *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Tokens $tokens
	 * @return void
	 */
	public function notify(Request $request, Tokens $tokens): void
	{
		$token = random_string(50, true);

		if (TokenMail::send($request->email, $token)) {
            $tokens->store($request->email, $token, Carbon::now()->addHour()->toDateTimeString());
			redirect()->back()->withAlert('success', __('password_reset_link_sent'))->go();
		} 
        
		redirect()->back()->withAlert('error', __('password_reset_link_not_sent'))->go();
	}
	
	/**
	 * reset password
	 *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Tokens $tokens
	 * @return void
	 */
	public function reset(Request $request, Tokens $tokens): void
	{
        if (!$request->has('email', 'token')) {
            response()->send('Bad Request', [], 400);
        }

        $reset_token = $tokens->findOneByEmail($request->email);

        if (!$reset_token || $reset_token->token !== $request->token) {
			response()->send(__('invalid_password_reset_link'), [], 400);
		}

		if ($reset_token->expire < Carbon::now()->toDateTimeString()) {
			response()->send(__('expired_password_reset_link'), [], 400);
		}

		$tokens->flush($reset_token->token);
		$this->render('auth.password.new', ['email' => $reset_token->email]);
	}
	
	/**
	 * update user password
	 *
     * @param  \Framework\Http\Request $request
     * @param  \App\Database\Repositories\Users $users
	 * @return void
	 */
	public function update(Request $request, Users $users): void
	{
		AuthRequest::validate($request->except('csrf_token'))->redirectOnFail();

        $users->updateBy(
            ['email', $request->email], 
            ['password' => pwd_hash($request->password)]
        );

        redirect()->url('login')->withAlert('success', __('password_reset'))->go();
	}
}
