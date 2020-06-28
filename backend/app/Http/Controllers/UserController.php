<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Validator;
use App\Helpers\Upload_image;

class UserController extends Controller
{
    //call this function and get user information
  protected $user;
  public function __construct() {
    $this->middleware(function ($request, $next) {
      $this->user= auth()->user();
      return $next($request);
    });
  }

  /**
   * [get user profile details description]
   * @Summary :-   [description]
   * @return  [type]
  */
  public function user_profile()
  {

   $user = $this->user;
   $user_profile = User::with(['master_country','master_state'])->where('user_id',$user->user_id)->first();

    if($user_profile['password'] == null)
    {
      $user_profile['is_password_set'] = 0;
    }
    else
    {
       $user_profile['is_password_set'] = 1;
    }

    return response()->json(['service_name' => 'my_profile','message'=>'','error'=>'','data'=>array('user_profile' => $user_profile)],200);

  }

  /**
   * [update_profile_post description]
   * @Summary :-   [description]
   * @return  [type]
  */

  public function update_user_profile(Request $request)
  {
    $user = $this->user;
    $data = User::select("*")->where('user_id',$user->user_id)->first();
    $error = array();
    $validator = Validator::make($request->all(), $user->rules);
    if ($validator->fails())
    {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'update_user_profile','global_error'=>$error],500);
    }
    else
    {

      $post_values['first_name']    = strip_tags(trim($request->post('first_name')));
      $post_values['last_name']     = strip_tags(trim($request->post('last_name')));
      // $post_values['user_name']     = strip_tags(trim($request->post('user_name')));
      $post_values['master_country_id'] =trim($request->post('master_country_id')) != "" ? trim($request->post('master_country_id')) : 0;
      $post_values['master_state_id'] = trim($request->post('master_state_id')) != "" ? trim($request->post('master_state_id')) : 0;

      // $post_values['phone_number']  = strip_tags(trim($request->post('phone_number')));

      if ($request->post('dob') !== null) {
        $post_values['dob'] = date('Y-m-d', strtotime($request->post('dob')));
        $dob_year = date('Y', strtotime($request->post('dob')));
        $current_date  = time_machine('today','Y-m-d');
        $current_year  = time_machine('today','Y');
        $date1 = $post_values['dob'];
        $date2 = $current_date;
        $diff = abs(strtotime($date2) - strtotime($date1));
        $years = floor($diff / (365*60*60*24));
        if($dob_year > $current_year){
          $error = "Date of birth should be greater than 18";
          return response()->json(['service_name' => 'update_user_profile','global_error'=>$error],500);
        }
        if($years < 18){
          $error = "Date of birth should be greater than 18";
          return response()->json(['service_name' => 'update_user_profile','global_error'=>$error],500);
        }

      }
      else
      {
        $post_values['dob'] = null;
      }
      $post_values['modified_date'] = time_machine();
      $condition = array('user_id' => $user->user_id);

      $result = User::where($condition)->update($post_values);
      return response()->json(['service_name' => 'update_user_profile','message'=>'Your profile updated successfully.','data'=>array()],200);
    }

  }

  /**
   * [add_update_password_post description]
   * @Summary :-   [description]
   * @return  [type]
   */
  public function update_user_password(Request $request)
  {
    /*$error    = array();
    $user_id = $this->user()->user_id;
    $current_user_password = Auth::user()->password;
    */
    $user = $this->user;
    $data = User::select("password")->where('user_id',$user->user_id)->first();
    $error = array();
    $validator = Validator::make($request->all(), $user->rules_change_password);
    if ($validator->fails())
    {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'update_user_password','global_error'=>$error],500);
    }
    else
    {
     $old_pass =base64_decode(trim($request->post('old_password')));
     $new_pass = bcrypt(base64_decode(trim($request->post('new_password'))));
      if (\Hash::check($old_pass, $user->password))
      {
        $condition = array('user_id' => $user->user_id);
        $updated = User::where($condition)->update(['password' => $new_pass]);
        if ($updated) {
          $message = 'Your password changed successfully.';
          return response()->json(['service_name' => 'update_user_password','message'=>$message],200);
        }
      }
      else
      {
        $error['old_password']   = 'You entered wrong old password.';
        return response()->json(['service_name' => 'update_user_password','global_error'=>$error],500);
      }
    }
  }

  /**
   * [update_user_profile_image_post description]
   * @Summary :-   [description]
   * @return  [type]
   */

  public function updated_user_profile_image(Request $request)
  {
    $user_id = $this->user->user_id;
    $condition = array('user_id' => $user_id);
    if(!empty($request->file('profileImage')) && $request->file('profileImage'))
    {
       $uploaded_image_response = Upload_image::upload_profile_picture_post($request->file('profileImage'),$user_id);
       if($uploaded_image_response['response_code'] == 500)
       {
          return $uploaded_image_response;
       }
       else
       {
          $post_values['image'] = $uploaded_image_response['data']['image'];
       }
      $result = User::where($condition)->update($post_values);
      return response()->json(['service_name' => 'upload_profile_image','message'=>'Your Profile Picture Updated successfully.','data'=>array('image' => $post_values['image'])],200);
    }
    else
    {
       return response()->json(['service_name' => 'upload_profile_image','message'=>'','error'=>'','global_error'=>'Image field is required'],500);
    }

  }


}
