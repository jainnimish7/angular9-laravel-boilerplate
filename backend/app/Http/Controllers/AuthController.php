<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Socialite;
use DB;
use Twilio\Rest\Client;
use Mail;
use Jenssegers\Agent\Agent as Agent;
use Redirect;


use App\Http\Controllers\MailController;

class AuthController extends Controller
{
  //
  public function __contstruct()
  {
    parent::__construct();
  }

  /**
   * [signup_post description]
   * @Summary :- [description]
   * @return  [type]
   */

  public function signup(Request $request)
  {
    if ($request->post()) {

      $referral_sender_id = '';
      $error       = array();
      $rules = [];
      $rules['user_name'] = 'required|max:20|min:3|unique:pgsql2.user,user_name|regex:/^[a-zA-Z0-9_-]{3,30}$/';
      $rules['email'] = 'required|unique:pgsql2.user,email|email|regex:/^(.+)@(.+)$/';
      $rules['password'] = 'required|min:6|max:100';
      $rules['confirm_password'] = 'required|min:6|max:100|same:password';
      $rules['dob'] = 'required';
      $rules['master_country_id'] = 'required|integer';
      $rules['master_state_id'] = 'required|integer';
      $rules['first_name'] = 'required|alpha|max:50|min:2';
      $rules['last_name'] = 'required|alpha|max:50|min:2';
      //user unique id of referral user
      if (isset($request->referral_code) && $request->referral_code) {
        $rules['referral_code'] = 'max:9|min:9';
      }

      $validator = Validator::make($request->all(), $rules);
      if ($validator->fails()) {
        $error = $validator->errors();
        $error_all = $validator->messages()->all();
        $message = $error_all[array_keys($error_all)[0]];
        return response()->json(['service_name' => 'signup', 'message' => $message, 'error' => $error, 'global_error' => $error], 422);
      } else {
        if (isset($request->referral_code) && $request->referral_code) {
          $referral_sender_data = User::select('user_unique_id', 'user_id')->where('user_unique_id', $request->referral_code)->first();
          if (empty($referral_sender_data)) {
            return response()->json(['service_name' => 'signup', 'global_error' => "This referral code isinvalid"],  500);
          } else {
            $referral_sender_id = $referral_sender_data['user_id'];
          }
        }

        $user_email = User::where('email', strtolower($request->email))->first();
        if (!empty($user_email)) {
          return response()->json(['service_name' => 'signup', 'message' => "Email Already exist"], 500);
        } else {
          $input = $request->post();
          $input['user_name'] = trim($input['user_name']);
          $input['first_name'] = trim($input['first_name']);
          $input['last_name'] = trim($input['last_name']);
          $input['email'] = strtolower($input['email']);
          $input['password'] = bcrypt(base64_decode($input['password']));
          $input['dob'] = $input['dob'];
          $input['user_unique_id'] = random_string('alnum', 9);
          $input['master_country_id']   = trim($input['master_country_id']) != "" ? trim($input['master_country_id']) : 0;
          $input['master_state_id']   = trim($input['master_state_id']) != "" ? trim($input['master_state_id']) : 0;
          $input['status'] = 2;
          $input['referred_by'] = trim($input['referral_code']);
          $input['remember_token'] = Str::random(60);
          $input['created_date'] = time_machine();
          $input['modified_date'] = time_machine();
          $reset_key = $input['user_unique_id'];
          $user = User::create($input);
          if ($user) {
            //send mail for user activation
            $time    = time();
            $back_data['link'] = base64_encode($reset_key . '_' . $time);
            $result = (new MailController)->send_registration_confirmation_link($input['email'], $back_data['link']);
            $response = array(
              'status' => TRUE,
              'message' => 'Mail sent you for activation'
            );
            return response()->json(['service_name' => 'signup', 'message' => 'Signup sucessfully.Please verify your email to login.', 'data' => $user], 200);
          } else {
            return response()->json(['service_name' => 'signup', 'message' => 'Invalid type', 'global_error' => 'Invalid type'], 400);
          }
        }
      }
    } else {
      return response()->json(['service_name' => 'signup', 'message' => 'Invalid parameter given', 'global_error' => 'Invalid parameter given'], 400);
    }
  }

  /**
   * [activate_account_get description]
   * @Summary :-  [description]
   * @return  [type]
   */

  public function activate_account($link)
  {
    if ($link == "") {
      return response()->json(['service_name' => 'activate_account', 'message' => 'This link is invalid', 'error' => '', 'global_error' => 'invalid_link'], 500);
    } else {
      $clink = base64_decode($link);
      $rec = explode("_", $clink);
      $unique_id = $rec[0];
      $user_record = User::select('user_id', 'email', 'first_name', 'status')->where('user_unique_id', $unique_id)->first();
      if (isset($user_record) && !empty($user_record)) {
        if ($user_record['status'] != 2) {
          return response()->json(['service_name' => 'activate_account', 'message' => 'This link is expired', 'error' => 'This link is expired'], 500);
        }
        $results = User::where('user_id', $user_record['user_id'])->update(array('status' => 1, 'modified_date' => time_machine(), 'status_reason' => 'Email address confirmed by user.'));
        if ($results) {
          return response()->json(['service_name' => 'activate_account', 'message' => 'Your account has been confirmed.', 'error' => ''], 200);
        } else {
          return response()->json(['service_name' => 'activate_account', 'message' => 'This link is invalid', 'error' => '', 'global_error' => 'invalid_link'], 500);
        }
      } else {
        return response()->json(['service_name' => 'activate_account', 'message' => 'This link is invalid', 'error' => '', 'global_error' => 'invalid_link'], 500);
      }
    }
  }


  public function get_user_data($input_arry = array())
  {
    // Start the query
    $query = User::query();

    if (array_key_exists('email', $input_arry) && $input_arry['email'] != '') {
      $query->orWhere('email', $input_arry['email']);
    }

    if (array_key_exists('facebook_id', $input_arry) && $input_arry['facebook_id'] != '') {
      $query->orWhere('facebook_id', $input_arry['facebook_id']);
    }

    if (array_key_exists('google_id', $input_arry) && $input_arry['google_id'] != '') {
      $query->orWhere('google_id', $input_arry['google_id']);
    }
    if (array_key_exists('user_name', $input_arry) && $input_arry['user_name'] != '') {
      $query->orWhere('user_name', $input_arry['user_name']);
    }
    $rs = $query->first();
    $result = $rs;
    return $result;
  }

  /**
   * [user_profile_data description]
   * @Summary :-  [description]
   * @return  [type]
   */

  public function user_profile_data($argument_array = array())
  {
    $select = array("user_id", "first_name", "last_name", "user_name", "email", "balance", "phone_number", "dob", "facebook_id", "pincode", "city", "image", "status", "last_ip", "last_login", "address_1", "address_2", "created_date", "modified_date", "opt_in_email", "status_reason");
    switch ($argument_array['profile_type']) {
      case 'facebook':
        $profile_data = User::select($select)->where('facebook_id', $argument_array['facebook_id'])->get();
        return $profile_data;
        break;
      case 'google':
        $profile_data = User::select($select)->where('google_id', $argument_array['google_id'])->get();
        return $profile_data;
        break;
      case 'native':
        $profile_data = User::select($select)->where('email', $argument_array['email'])->orWhere('user_name', $argument_array['email'])->orWhere('password', $argument_array['password'])->first();
        return $profile_data;
        break;

      default:
        break;
    }
  }

  /**
   * [login_post description]
   * @Summary :- [description]
   * @return  [type]
   */
  public function user_login(Request $request)
  {
    $post_data = $request->post();
    if (array_key_exists('social_type', $post_data) && $post_data['social_type'] == 'facebook') {
      $post_data['facebook_id'] = $post_data['social_id'];
      return $this->social_login($post_data);
    } elseif (array_key_exists('social_type', $post_data) && $post_data['social_type'] == 'google') {
      $post_data['google_id'] = $post_data['social_id'];
      return  $this->social_login($post_data);
    } else {
      return $this->custom_login($request);
    }
  }


  /**
   * [social_login description]
   * @Summary :-  [description]
   * @return  [type]
   */

  protected function social_login($post_data = array())
  {
    if ($post_data) {
      $validator = Validator::make(
        $post_data,
        [
          'social_id' => 'required',
        ]
      );

      if ($validator->fails()) {
        $error = $validator->errors();
        $error_all = $validator->messages()->all();
        $message = $error_all[array_keys($error_all)[0]];
        return response()->json(['response_code' => 500, 'service_name' => 'user_login', 'message' => $message, 'error' => $error, 'global_error' => $error]);
      }

      $data = array();
      $social_type = "";
      $user_social_id = '';

      if (isset($post_data['facebook_id']) && $post_data['facebook_id']) {
        $social_type = "facebook";
        $data['email'] = $post_data['email'];
        $data['first_name'] = $post_data['first_name'];
        $data['last_name'] = $post_data['last_name'];
        $data['facebook_id'] = $post_data['facebook_id'];
        $data['image'] = $post_data['image'];
        $data['user_unique_id'] = random_string('alnum', 9);
        $user_social_id = $post_data['facebook_id'];
      }

      if (isset($post_data['google_id']) && $post_data['google_id']) {
        $social_type = "google";
        $data['email'] = $post_data['email'];
        $data['first_name'] = $post_data['first_name'];
        $data['last_name'] = $post_data['last_name'];
        $data['google_id'] = $post_data['google_id'];
        $data['image'] = $post_data['image'];
        $data['user_unique_id'] = random_string('alnum', 9);
        $user_social_id = $post_data['google_id'];
      }

      $data['status'] = 1;
      $data['created_date'] = date('Y-m-d H:i:s');
      $data['last_login'] = date('Y-m-d H:i:s');
      $data['last_ip'] = \Request::ip();

      $email = $data['email'];
      $user_data = $this->get_user_data($post_data);
      if (empty($user_data)) {
        // Registration for new user
        //$reset_key = $post_data['user_unique_id'] = self::_generate_key();
        if (isset($data['email']) && $data['email'] == "") {
          unset($data['email']);
        }

        $user = User::create($data);

        $data['user_id'] = $user->user_id;


        if ((isset($data['facebook_id']) && $data['facebook_id'] != "")) {
          $response = array(
            'response_code' => 200,
            'status'   => TRUE,
            'message'      => 'Thank you for registering with us.',
            'acc_type' => 'facebook',
            'data'     => $data
          );
        } else if ((isset($data['google_id']) && $data['google_id'] != "")) {
          // $this->add_notifications($email, $inserted_id);
          $response = array(
            'response_code' => 200,
            'status'   => TRUE,
            'message'      => 'Thank you for registering with us.',
            'acc_type' => 'google',
            'data'     => $data
          );
        } else {
        }
      } else {
        if (array_key_exists('email', $data) &&  $data['email'] == $user_data['email']) {
          // Email already exist in db
          // Check user comes as a  facebook user
          if ((isset($data['facebook_id']) && $data['facebook_id'] != "")) {
            $update_data = array('facebook_id' => $data['facebook_id'], 'last_login' => date('Y-m-d H:i:s'));
            $where = array('user_id' => $user_data['user_id']);
            $result =  User::where($where)->update($update_data);

            $response = array(
              'status'   => TRUE,
              'message'      => '',
              'acc_type' => 'facebook',
              'data'     => $user_data
            );
          } else if ((isset($data['google_id']) && $data['google_id'] != "")) {
            // Check user comes as a google user
            $update_data = array('google_id' => $data['google_id'], 'last_login' => date('Y-m-d H:i:s'));
            $where  = array('user_id' => $user_data['user_id']);
            $result =  User::where($where)->update($update_data);
            $response = array(
              'status'   => TRUE,
              'message'      => '',
              'acc_type' => 'google',
              'data'     => $user_data
            );
          } else {
            $response = array(
              'status' => FALSE,
              'message'    => 'Email Already exists'
            );
          }
        } else if (array_key_exists('user_name', $data) && $data['user_name'] == $user_data['user_name']) {
          $response = array(
            'status' => FALSE,
            'message'    => 'User Name Already exists'
          );
        } else if (isset($data['facebook_id']) && $data['facebook_id'] == $user_data['facebook_id']) {
          $update_data = array('last_login' => date('Y-m-d H:i:s'));
          $where       = array('user_id' => $user_data['user_id']);
          $result =  User::where($where)->update($update_data);
          $response = array(
            'response_code' => 200,
            'status'   => TRUE,
            'message'      => '',
            'acc_type' => 'facebook',
            'data'     => $user_data
          );
        } else if (isset($data['google_id']) && $data['google_id'] == $user_data['google_id']) {
          $update_data = array('last_login' => date('Y-m-d H:i:s'));
          $where       = array('user_id' => $user_data['user_id']);
          $result =  User::where($where)->update($update_data);
          $response = array(
            'response_code' => 200,
            'status'   => TRUE,
            'message'      => '',
            'acc_type' => 'google',
            'data'     => $user_data
          );
        }
        $output = $response;
        if ($output['status']) {

          $profile_data = array();
          if ($social_type == 'facebook') {

            $profile_data = $this->user_profile_data(array('profile_type' => 'facebook', 'facebook_id' => $post_data['facebook_id']));
          }
          if ($social_type == 'google') {

            $profile_data = $this->user_profile_data(array('profile_type' => 'google', 'google_id' => $post_data['google_id']));
          }



          $accessToken =  $this->issueToken($user_data);

          $response = array(
            'response_code' => 200,
            'service_name' => 'user_login',
            'message' => 'user login successfully',
            'token' => $accessToken,
            'data' => $data,

          );

          //Remove Null value from array
          $profile_data = remove_null_values($profile_data);
          $data = array('profile_data' => $profile_data);
          if ($redirect_url) {
            // $data['redirect_url'] = $redirect_url;
          }
        } else {
          $response = array(
            'response_code' => 500,
            'service_name' => 'user_login',
            'message' => $output['message'],
            'global_error' => $output['message'],
            'error' => array()
          );
        }
      }

      return response()->json($response);
    } else {
      return response()->json(['response_code' => 405, 'service_name' => 'user_login', 'message' => 'No Post Data Found', 'global_error' => 'No Post Data Found', 'error' => array()]);
    }
  }


  /**
   * [custom_login description]
   * @Summary :-  [description]
   * @return  [type]
   */
  protected function custom_login($request)
  {
    if ($request->post()) {
      $validator = Validator::make(
        $request->all(),
        [
          'email' => 'required',
          'password' => 'required',
        ]
      );

      //get email and password
      $login_data  = trim($request->post('email'));
      $password = base64_decode(trim($request->post('password')));
      //$remember_me = $request->post('remember_me');

      if ($validator->fails()) {
        $error = $validator->errors();
        $error_all = $validator->messages()->all();
        $message = $error_all[array_keys($error_all)[0]];
        return response()->json(['service_name' => 'user_login', 'message' => $message, 'error' => $error, 'global_error' => $error], 422);
      } else {
        $email =  $request->post('email');
        $profile_data = $this->user_profile_data(array('profile_type' => 'native', 'email' => $email, 'phone_number' => $email, 'password' => $password));

        $update_data = array('last_login' => date('Y-m-d H:i:s'), 'last_ip' => \Request::ip());
        $where = array('user_id' => $profile_data['user_id']);
        $result =  User::where($where)->update($update_data);
        $profile_data = remove_null_values($profile_data);
        if (isset($profile_data) && !empty($profile_data)) {
          if ($profile_data['status'] != 3) {
            if ($profile_data['status'] == 1) {
              if (filter_var($login_data, FILTER_VALIDATE_EMAIL)) {
                //user sent their email
                Auth::attempt(['email' => $login_data, 'password' => $password]);
              } else if (is_numeric($login_data)) {
                //they sent their mobile instead
                Auth::attempt(['phone_number' => $login_data, 'password' => $password]);
              } else {
                Auth::attempt(['user_name' => $login_data, 'password' => $password]);
              }

              if (Auth::check()) {

                $user = Auth::user();
                $success['token_type'] =  'Bearer';
                $success['access_token'] = $user->createToken('UrgentFury')->accessToken;
                $data =  $profile_data;

                return response()->json(['service_name' => 'user_login', 'message' => 'user login successfully', 'token' => $success, 'data' =>
                $data], 200);
              } else {
                $error['email'] = 'Invalid login details';
                return response()->json(['service_name' => 'user_login', 'message' => 'Invalid login details', 'error' => $error, 'global_error' => 'Invalid login details'], 500);
              }
            } else {
              if ($profile_data['status'] == 2) {

                $error = array('email' => $profile_data['status_reason']);
                $error_msg = ($profile_data['status_reason']) ? $profile_data['status_reason'] : 'Email is not confirmed.';

                return response()->json(['service_name' => 'user_login', 'message' => $error_msg, 'error' => $error_msg, 'global_error' => $error_msg], 500);
              } else if ($profile_data['status'] == 4) {

                $error_msg = ($profile_data['status_reason']) ? $profile_data['status_reason'] : 'Your account is permanently deleted.';
                $error = array('email' => $profile_data['status_reason'], 'password' => '');

                return response()->json(['service_name' => 'user_login', 'message' => $profile_data['status_reason'], 'error' => $error, 'global_error' => $error_msg], 500);
              } else if ($profile_data['status'] == 0) {
                $error_msg = ($profile_data['status_reason']) ? $profile_data['status_reason'] : 'Your account is inactive please contact to admin to activate your account.';
                $error = array('email' => $profile_data['status_reason'], 'password' => '');

                return response()->json(['service_name' => 'user_login', 'message' => $profile_data['status_reason'], 'error' => $error, 'global_error' => $error_msg], 500);
              } else if ($profile_data['status'] == 5) {
                $error_msg = ($profile_data['status_reason']) ? $profile_data['status_reason'] : 'You self exculded from your account please create new account or contact to admin.';
                $error = array('email' => $profile_data['status_reason'], 'password' => '');

                return response()->json(['service_name' => 'user_login', 'message' => $profile_data['status_reason'], 'error' => $error, 'global_error' => $error_msg], 500);
              } else if ($profile_data['status'] == 6) {
                $error_msg = ($profile_data['status_reason']) ? $profile_data['status_reason'] : 'Your Timeout period not completed yet.';
                $error = array('email' => $profile_data['status_reason'], 'password' => '');
                return response()->json(['service_name' => 'user_login', 'message' => $profile_data['status_reason'], 'error' => $error, 'global_error' => $error_msg], 500);
              }
            }
          } else {
            $message = 'Your account has been banned. Contact admin@urgentfury.com for more information';

            $error = array('email' => $message, 'password' => '', 'reason' => $profile_data['status_reason']);

            return response()->json(['service_name' => 'user_login', 'message' => $message, 'error' => $error, 'global_error' => $message], 500);
          }
        } else {
          $error['email'] = 'Invalid login details';
          return response()->json(['service_name' => 'user_login', 'message' => 'Invalid login details', 'error' => $error, 'global_error' => 'Invalid login details'], 500);
        }
      }
    } else {
      $error = array('email' => 'Invalid parameter given');
      return response()->json(['service_name' => 'user_login', 'message' => 'Invalid parameter given', 'error' => $error, 'global_error' => $error], 400);
    }
  }


  /**
   * [logout_post description]
   * @Summary :-  LOGOUT SERVICE TO REMOVE CURRENT USER SESSION
   * @return  [type]
   */

  public function logout(Request $request)
  {
    $request->user()->token()->delete();
    return response()->json(['response_code' => 200, 'service_name' => 'logout', 'message' => 'logout successfully', 'data' => array()]);
  }

  /**
   * [forgot_password_post description]
   * @Summary :-  [description]
   * @return  [type]
   */
  public function forgot_password(Request $request)
  {
    if ($request->post()) {
      $validator = Validator::make(
        $request->all(),
        [
          'email' => 'required|email|regex:/^(.+)@(.+)$/',
        ]
      );
      if ($validator->fails()) {
        $error = $validator->errors();
        $error_all = $validator->messages()->all();
        $message = $error_all[array_keys($error_all)[0]];
        return response()->json(['service_name' => 'forgot_password', 'message' => '', 'error' => '', 'global_error' => $error], 500);
      } else {
        $user_email = User::where('email', strtolower($request->email))->first();
        if (empty($user_email)) {
          return response()->json(['service_name' => 'forgot_password', 'message' => "Email Not exist, Please try Signup instead."], 500);
        } else {
          $reset_key = $user_email->user_unique_id;
          $time = time() + (24 * 60 * 60);
          $datetime = date('Y-m-d H:i:s');
          $input = $request->post();
          $email = strtolower($input['email']);
          $time    = time();
          //send mail for user forgot password link

          $back_data['link'] = base64_encode($reset_key . '_' . $time);
          User::where('email', $email)->update(array('new_password_key' => $reset_key, 'new_password_requested' => $datetime, 'modified_date' => time_machine()));

          $result = (new MailController)->send_user_forgot_password_reset_link($email, $back_data['link']);
          return response()->json(['service_name' => 'forgot_password', 'message' => 'An reset password link has been sent to your registered email address.'], 200);
        }
      }
    } else {
      return response()->json(['service_name' => 'forgot_password', 'message' => '', 'error' => '', 'global_error' => 'Invalid parameter given'], 500);
    }
  }

  /**
   * [reset_password_post description]
   * @Summary :-  [description]
   * @return  [type]
   */

  public function reset_password(Request $request)
  {
    if ($request->post()) {
      $validator = Validator::make(
        $request->all(),
        [
          'unique_token'     => 'required',
          'password'      => 'required|min:6|max:100',
          'confirm_password'  => 'required||min:6|max:100|same:password',
        ]
      );
      if ($validator->fails()) {
        $error = $validator->errors();
        $error_all = $validator->messages()->all();
        $message = $error_all[array_keys($error_all)[0]];
        return response()->json(['service_name' => 'reset_password', 'message' => '', 'error' => '', 'global_error' => $error], 500);
      } else {
        $clink = base64_decode($request->post('unique_token'));
        $rec = explode("_", $clink);
        $unique_id = $rec[0];
        $row_result = USER::select('user_id', 'email', 'new_password_key', 'status')->where('new_password_key', $unique_id)->first();

        $new_insert_password = base64_decode(trim($request->post('password')));
        $user_record = User::where('user_unique_id', $unique_id)->first();
        if ($user_record)

          if (isset($row_result) && !empty($row_result)) {
            if (isset($user_record) && !empty($user_record)) {
              $results = User::where('user_unique_id', $unique_id)->update(array('new_password_key' => null, 'new_password_requested' => null, 'password' => bcrypt($new_insert_password), 'modified_date' => time_machine()));
              if ($results) {
                return response()->json(['service_name' => 'reset_password', 'message' => 'Password Changed Successfully.'], 200);
              } else {
                return response()->json(['service_name' => 'reset_password', 'message' => 'Password not changed.'], 500);
              }
            } else {
              $message = "Invalid parameters given.";
              return response()->json(['service_name' => 'reset_password', 'message' => '', 'global_error' => $message], 500);
            }
          } else {
            $error = 'This link is expired';
            return response()->json(['service_name' => 'reset_password', 'message' => $error, 'global_error' => $error], 500);
          }
      }
    } else {
      $error = array('message' => 'Invalid parameter given');
      return response()->json(['response_code' => 400, 'service_name' => 'reset_password', 'message' => $error, 'error' => '', 'global_error' => $error]);
    }
  }


  //create token for social login
  private function issueToken(User $user)
  {

    $userToken = $user->token() ?? $user->createToken('socialLogin');
    return [
      "token_type" => "Bearer",
      "access_token" => $userToken->accessToken
    ];
  }
}
