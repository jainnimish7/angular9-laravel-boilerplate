<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
# Models
use App\Models\Leagues;
use App\Models\User;
use App\Models\Contest;
use App\Models\SubContest;
// use App\Models\Contest_User_Prime;
use App\Models\Linup_Master;
use App\Models\PaymentHistoryTransaction;
use Validator;
use DB;

class LobbyController extends Controller
{
  public function get_contest_list(Request $request)
  {
    $this->user = Auth::user();
    \DB::enableQueryLog();
    $contest = new Contest;

    $contest = $contest->with('season','season.league','game_style')
    ->where(function($w){
      $w->where('user_id', 0)
      ->orWhere(function($w){
        $w->where('user_id', '!=', 0)
        ->where('is_private', 0);
      });
    });

    $contest = $contest->whereDoesntHave('sub_contests.lineup_masters', function($wh){
      //$wh-> ('lineup_masters', function($q){
        $wh->where('user_id', $this->user->user_id);
      //});
    });

    if(isset($request->keyword) && $request->keyword != '')
    {
      $contest = $contest->where('contest_name', 'ilike', '%'.$request->keyword.'%')
      ->orWhere('contest_uid', 'ilike', '%'.$request->keyword.'%');
    }
    $contest = $contest->whereHas('season', function($q) use ($request){
      if(isset($request->league_id) && $request->league_id > -1)
      {
        $q->where('league_id', $request->league_id);
      }
    });

    $contest = $contest->whereHas('game_style', function($q) use ($request){
      if($request->game_style)
      {
        $q->where('abbr', $request->game_style);
      }
    });

    if( $request->draft_speed ){
      $contest = $contest->where('draft_speed', $request->draft_speed);
    }

    if(isset($request->min_entry_fee) && isset($request->max_entry_fee))
    {
       $contest = $contest->whereBetween('entry_fees', [$request->min_entry_fee, $request->max_entry_fee]);
    }

    $contest = $contest->where('status',1);
    $contest = $contest->get();

    if($contest->count() == 0) {
      return response()->json([
        'service_name' => 'contest_list',
        'global_error'=> 'No contest found',
      ], 404);
    }

    return response()->json([
      'service_name' => 'contest_list',
      'data' => $contest,
      'message'=> 'Contests found',
    ], 200);
  }

  //min max prize get for contest
  public function min_max_entry_fees() {
    $all_fee = Contest::selectRaw("MAX(entry_fees) AS max_fee, MIN(entry_fees) AS min_fee")->get();
    return response()->json([
      'service_name' => 'contest_entry_fee',
      'data' => $all_fee,
      'message'=> 'Contests Fee found',
    ], 200);
  }

  //join contest
  public function join_contest(Request $request)
  {
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    $rules = [];
    $rules['entry_fee'] = 'required';
    $rules['contest_size'] = 'required';
    $rules['league_id'] = 'required';
    $rules['contest_id'] = 'required';
    $rules['team_name'] = 'required';

    $customMessages = [
        'entry_fee.required' => 'Entry Fee is required',
        'contest_size.required' => 'Contest size is required',
        'league_id.required' => 'League Id is required',
        'contest_id.required' => 'Contest Id is required',
        'team_name.required' =>'Team Name is required'
    ];

    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'join_contest', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }

    // check for users profile completed
    $profile_completed = $this->is_profile_completed($user_id);
    if(!$profile_completed)
    {
      $error = 'Please complete your profile information.';
      return response()->json(['service_name' => 'join_contest', 'message' => $error, 'error' =>'', 'global_error' => $error], 500);
    }

    $entry_fee = $request->post('entry_fee');
    $contest_id = $request->post('contest_id');
    $contest_size = $request->post('contest_size');
    $team_name = $request->post('team_name');

    //create sub contest
    $data = array(
      'contest_id'=>$contest_id,
    );

    //get_sub_contest data behalf contest
    $check_exit_sub_contest = SubContest::where('contest_id',$contest_id)->where('total_user_joined', '!=', 12)->first();

    if(!$check_exit_sub_contest || $check_exit_sub_contest['size'] < $check_exit_sub_contest['total_user_joined']+1)
    {
        $sub_contest_create = $this->create_sub_contest($data);
        $sub_contest_id =$sub_contest_create->sub_contest_id;
    }
    else
    {
       $sub_contest_id = $check_exit_sub_contest->sub_contest_id;
    }
    $sub_contest = SubContest::where('contest_id',$contest_id)->first();

    //contest_exit
    $contest = $this->get_contest_by_id($contest_id);

    if ($contest['status'] != 1) {
      $error = 'This competiton has been ended.';
      return response()->json(['service_name' => 'join_contest', 'message' => $error, 'error' =>'', 'global_error' => $error], 500);
    }

    // check for user enough balance
    $has_balance = $this->has_enough_balance($user_id, $entry_fee);
    if (!$has_balance['status']) {
      $error = 'You have insufficient funds to enter this competition, Please choose one of lesser value or add funds to your account.';
      return response()->json(['service_name' => 'join_contest', 'message' => $error, 'error' =>'', 'global_error' => $error], 500);
    }

    //check if contest_user_prime already exits userid-subcontestid
    $already_contest_user_prime = Linup_Master::where('sub_contest_id',$sub_contest_id)->where('user_id',$user_id)->first();
    if($already_contest_user_prime)
    {
      $error = 'Already Joined';
      return response()->json(['service_name' => 'join_contest', 'message' => $error, 'error' =>'', 'global_error' => $error], 500);
    }

    // create entry in contest_user table
    $contest_user = array(
      'sub_contest_id' => $sub_contest_id,
      'user_id' => $user_id,
      'team_name' =>$team_name,
      'status' => 1,
      'joined_date' => time_machine(),

    );
    $user_contest_entry = $this->save_contest_user($contest_user);

    // transaction entry
    $transaction = $this->get_transaction_entry($contest_id,$user_id, $entry_fee, $has_balance);
    $payment_history_id = PaymentHistoryTransaction::create($transaction);

    //update user balance
    $balance = $has_balance['balance_amount'] - $has_balance['deducted_user_balance'];
    $winning_bal = $has_balance['winning_balance_amount'] - $has_balance['deducted_winning_balance'];
    $update_data = array('balance' => $balance, 'winning_balance' => $winning_bal);
    $updated_user = User::where('user_id',$user_id)->update($update_data);

    //increment joined_contest
    $updated = $this->update_contest($sub_contest_id);
    if (!$updated) {
      $error = 'invalid parameter given';
        return response()->json(['service_name' => 'join_contest', 'message' => $error, 'error' =>'', 'global_error' => $error], 500);
    }
    $data = array(
      'message' => 'You have successfully joined this competition, good luck.',
      'balance' => $update_data['balance'] + $update_data['winning_balance'],
      'sub_contest_id' => $sub_contest_id,
    );
    return response()->json(['service_name' => 'join_contest', 'message' => $data], 200);
  }

  //check profile complete or not
  private function is_profile_completed($user_id)
  {
    $result = User::where('user_id',$user_id)->where(function($q){
    return $q->whereNull('user_name')
       ->orWhereNull('first_name')
       ->orWhereNull('last_name')
       ->orWhereNull('dob')
       ->orWhereNull('master_country_id');
    })->first();

    return empty($result) ? true : false;

  }

  //check contest exit or not
  private function get_contest_by_id($contest_id)
  {
    $contest =Contest::where('contest_id',$contest_id)->first();
    return $contest;
  }

  //check user has enough balance
  private function has_enough_balance($user_id,$entry_fee)
  {
    $user = User::where('user_id',$user_id)->first();
    $result['status'] = true;
    $result['balance_amount'] = $user['balance'];
    if ($user['balance'] + $user['winning_balance'] < $entry_fee)
      return $result['status'] = false;
    if ($user['balance'] >= $entry_fee) {
      $result['deducted_user_balance'] = $entry_fee;
      $result['winning_balance_amount'] = $user['winning_balance'];
      $result['deducted_winning_balance'] = 0;
      return $result;
    } elseif ($user['balance'] + $user['winning_balance'] >= $entry_fee) {
      $result['winning_balance_amount'] = $user['winning_balance'];
      $result['deducted_user_balance'] = $user['balance'];
      $result['deducted_winning_balance'] = $entry_fee - $user['balance'];
      return $result;
    }
  }

  //save contest user prime
  private function save_contest_user($contest_user)
  {
    $contest_id = Linup_Master::create($contest_user);
    return $contest_id;
  }

  //get tranaction entry
  public function get_transaction_entry($contest_id,$user_id, $entry_fee, $has_balance)
  {
    return array(
      'contest_id' => $contest_id,
      'user_id' => $user_id,
      'amount' => $entry_fee,
      'payment_for' => 1,
      'description' => 'Join Contest',
      'payment_type' => 1,
      'created_date' => time_machine(),
      'deducted_winning_balance' => $has_balance['deducted_winning_balance'],
      'deducted_user_balance' => $has_balance['deducted_user_balance'],
      'is_processed' => 1,
    );
  }

  //create sub-contest
  public function create_sub_contest($data)
  {
    $data_arr = array(
      'sub_contest_uid' => random_string('alnum', 9),
      'contest_id'=> $data['contest_id'],
      'total_user_joined'=>0,
      'draft_date_time' => time_machine(),
      'created_date' => time_machine(),
      'modified_date'=>time_machine(),
      'status' => 1,
      'size'=>12
    );
    $create_sub_contest = SubContest::create($data_arr);
    return $create_sub_contest;
  }

  //update contest total user joined
  public function update_contest($sub_contest_id)
  {
    //return $this->node_request();
    $sub_contest = SubContest::where('sub_contest_id',$sub_contest_id)->first();
    //add 15 miunte in draft_date_time when contest size equal total_user_joined
    $minutes_to_add = 15;
    $current_date = time_machine();
    $time = new \DateTime($current_date);
    $increment_time = $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
    $draft_date_time = $increment_time->format('Y-m-d H:i:s');
    if($sub_contest['size'] == $sub_contest['total_user_joined']+1)
    {
      $update_data = array(
      'total_user_joined' =>DB::raw('total_user_joined + 1'),
      'draft_date_time' => $draft_date_time,
      'modified_date' => $current_date);
    }
    else
    {
      $update_data = array(
      'total_user_joined' =>DB::raw('total_user_joined + 1'),
      'modified_date' => $current_date);
    }
    $update_contest_increment = SubContest::where('sub_contest_id',$sub_contest_id)->update($update_data);
    return $update_contest_increment;
  }

  //function call node curl request
  public function node_request(Request $request)
  {
    $url           = $request->url;
    $authorization = $request->authorization;
    $result = Contest::select('contests.contest_uid','contests.draft_speed','game.sub_contests.sub_contest_id','game.sub_contests.draft_date_time')->leftJoin('game.sub_contests', 'contests.contest_id', '=', 'sub_contests.contest_id')->get();
    if($result){
      foreach($result as $key){
          $lineupResult = Linup_Master::leftJoin('game.lineups', 'lineups.lineup_master_id', '=', 'lineup_masters.lineup_master_id')->where('lineup_masters.sub_contest_id', $key->sub_contest_id)->whereNotNull('game.lineups.lineup_master_id')->orderBy('lineups.round_number', 'asc')->orderBy('lineups.pick_number', 'asc')->get();
          if($lineupResult){
            foreach($lineupResult as $lm){
              $lineupIdArray[] =  $lm->lineup_id;
            }
          }
        }

        $data = array(
            'uid'            => $key->contest_uid.':'.$key->sub_contest_id,
            'timerSpan'      => $key->draft_speed * 1000,
            'startTime'      => '',
            'endTime'        => '',
            'currentLineupId'=> '',
            'draftStartAt'   => strtotime($key->draft_date_time),
            'lineupIds'      => array_reverse($lineupIdArray)
        );

        $postdata = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$authorization));
        $response = curl_exec($ch);
        curl_close($ch);
        // echo $response;
        return $response;
      }


  }
}
