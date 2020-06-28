<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Mail;

class MailController extends Controller
{

	public function send_registration_confirmation_link($to, $link)
	{
  	 	$rows = User::select("*")->where('email',$to)->first();
		$data['username'] = ($rows['user_name'] == "") ? $rows['first_name'] : $rows['user_name'];
		$data['link'] =env("APP_URL_FRONT") . "/activate-account/" . $link;
		Mail::send('emails.signup_emailer', $data, function($message) use ($data,$to) {
		        $message->to($to);
		        $message->subject('Welcome and thanks for your Registration');
		    });
	}

	public function send_user_forgot_password_reset_link($to, $link)
    {
        $rows = User::select("*")->where('email',$to)->first();
		$data['username'] = ($rows['user_name'] == "") ? $rows['first_name'] : $rows['user_name'];
		$data['link'] =env("APP_URL_FRONT") . "/reset-password/" . $link;
		Mail::send('emails.forgot_pass_mail', $data, function($message) use ($data,$to) {
              $message->to($to);
              $message->subject('Request for reset password.');
        });
    }
}
