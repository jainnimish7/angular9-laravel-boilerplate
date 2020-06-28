<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Linup_Master;
use App\Models\Contest;
use App\Models\Master_Contest_Roster;
use Validator;
use Auth;
use App\Helpers\Prize_evaluate;
use App\Http\Controllers\CommonController;
use App\Rules\Contest\EndDate;
use App\Models\Leagues;
use App\Models\Master_Prize;
use App\Models\GameStyle;
use App\Models\Seasons;
use App\Models\MatchWeek;
use App\Models\Match;

class ContestController extends Controller
{
  //define here constructor
  protected $common;
  public function __construct(CommonController $common)
  {
    $this->common = $common;
  }
  //create contest_post api for create new contest
  public function create_contest(Request $request)
  {
    $contestDetails = $request->post('contest_details');

    $validator = Validator::make($contestDetails, [
      "contest_name" => ['required', 'min:6', 'max:30'],
      "league" => ['required'],
      "entry_fees" => ['required', "numeric", "min:10", "max:10000"],
      "contest_size" => ['required','numeric','between:8000,11520'], //game_size
      "start_date" => ['required'],
      "end_date"=>['required','after:start_date'],
      "game_type" => ['required'], //master_game_style_id
      "prize_payout" => ['required'],
      "lineup_style" => ['required'],
      "draft_type" => ['required'],
      "draft_speed" => ['required'],
      "play_off_date" =>['required'],
      //"prize_pool" => ['required'],
      "when_to_draft" =>['required'],
     //"projected_drafting_end_date" => ['required'],

    ]);
    if($validator->fails()){
      return response()->json([
        'response_code'=> 400,
        'service_name' => 'create_contest',
        'message'=> 'Validation Failed',
        'global_error'=> $validator->errors()->first(),
      ], 400);
    }
    $league = Leagues::with('season')->where('league_id', $contestDetails['league'])->first();
    $this->user = Auth::user();
    $user_id = $this->user->user_id; //get user id
    $SiteRake = 9; // set Siterake 

    $projected_drafting_end_date = $this->calculated_drafting_date_time($contestDetails);
    $projected_drafting_end_date['response_code'];
    if($projected_drafting_end_date['response_code'] == 400)
    {
      return $projected_drafting_end_date;
    }

    $newContest = Contest::create([
      "contest_uid" => random_string(),
      "contest_name" => $contestDetails['contest_name'],
      "start_date" => $contestDetails['start_date'],
      "end_date" => $contestDetails['end_date'],
      "season_id" => $league->season->season_id,
      "status" => 1,
      "entry_fees" => $contestDetails['entry_fees'],
      "master_prize_id" => $contestDetails['prize_payout'],
      'master_game_style_id'=>$contestDetails['game_type'],
      "default_sub_contest_size"=>12,
      "size" => $contestDetails['contest_size'], //game_size
      "is_private" => $contestDetails['is_private'],
      "prize_pool" => $this->calculate_prize_pool($contestDetails,$SiteRake),
      "user_id" => $user_id,
      "projected_drafting_end_date" => $projected_drafting_end_date['data'],
      "when_to_draft" => $contestDetails['when_to_draft'],// 1 = When Filled, 2 = Scheduled
      "draft_type"=> $contestDetails['draft_type'], // default live snake draft - 1
      // Fast = 30 secs, Regular = 60 secs, Slow = 8 Hours
      "draft_speed" => $contestDetails['draft_speed'], 
      //1=Standard, 2=Superflex
      "lineup_style" => ($contestDetails['lineup_style'] == 'Standard') ? 1 : 2,
      "playoff_date" => $contestDetails['play_off_date'],//Playoff Date must be between Start Date & End Date
      "site_rake" => $SiteRake / 100, //Convert our percentage value into number
      "create_date" => time_machine(),
      "modified_date" => time_machine()
    ]);

    return response()->json([
      'response_code'=> 200,
      'service_name' => 'create_contest',
      'message'=> 'Contest Created Successfully',
    ]);
  }

  //prize pool
  private function calculate_prize_pool($contestDetails,$SiteRake){
    $siteRake = ($contestDetails['contest_size'] * $contestDetails['entry_fees']) * $SiteRake / 100;
    $prize_pool = ($contestDetails['contest_size'] * $contestDetails['entry_fees']) - $siteRake;
    return $prize_pool;
  }

  //calculate drafting date and time
  public function calculated_drafting_date_time($contestDetails)
  {
    $calculate_seconds = $contestDetails['contest_size'] * $contestDetails['lineup_style'] * $contestDetails['draft_speed']; //get seconds
    $start_date =  new \DateTime("@0");
    $seconds = new \DateTime("@$calculate_seconds"); // seconds convert number of day
    $calculate_days = $start_date->diff($seconds)->format('%a');

    //get touranament date array
    $tournament_date_arr = $contestDetails['tournament_date'];
    $get_earliest_date = min($tournament_date_arr);
    //get date
    $get_drafting_end_date = date("Y-m-d h:i:s", strtotime("-".$calculate_days. "days",   strtotime($get_earliest_date)));
    //current_date
     $current_date = time_machine();
    if($get_drafting_end_date > $current_date)
    {
      $response['response_code'] = 200;
      $response['service_name']  = "'projected_drafting_end_date";
      $response['data']       = $get_drafting_end_date;
      return $response;
    }
    else
    { 
      $response['response_code'] = 400;
      $response['service_name']  = "'projected_drafting_end_date";
      $response['message']       = '';
      $response['global_error']    = "Project drafting end date must be grater than current date. Please select properly tournament or drafting details.";
      return $response;
    }
  }

  //get match week show for nfl
  public function get_match_weeks($season_id, $game_style_id){
    $season = Seasons::where('season_id', $season_id)->first();
    $gameStyle = GameStyle::where('master_game_styles_id', $game_style_id)->first();
    if($season && $gameStyle){
     return $matchWeeks = $this->construct_weeks($season, $gameStyle);

      if( count($matchWeeks) > 0 ){

        return response()->json([
          'response_code'=> 200,
          'service_name' => 'get_match_weeks',
          'data' => $matchWeeks,
          'message'=> 'Pre Data Fetched',
        ]);

      }
    }
    return response()->json([
      'response_code'=> 404,
      'service_name' => 'get_match_weeks',
      'global_error'=> 'No match weeks found',
    ], 404);
  }

  private function construct_weeks($season, $gameStyle)
  {
    $weeks = [];

    if( strtolower($season->league->league_url_name) === 'nfl')
    {
      $weeks = MatchWeek::where('season_id', $season->season_id)
      ->where('start_date_time', '>', format_date())
      ->get();

    }
    else if( strtolower($season->league->league_url_name) === 'nfl' && $gameStyle->abbr === 'bestball' ){

      // Create instance for all weeks
      $allWeeks = MatchWeek::where('season_id', $season->season_id);

      $option1 = $this->subquery_for_ranged_weeks($allWeeks)->first();

      // Create instance for all weeks skipping 4 records
      $allWeeks = MatchWeek::where('season_id', $season->season_id)->skip(4);

      $option2 = $this->subquery_for_ranged_weeks($allWeeks)->first();

      // Create instance for all weeks skipping 8 records
      $allWeeks = MatchWeek::where('season_id', $season->season_id)->skip(8);

      $option3 = $this->subquery_for_ranged_weeks($allWeeks)->first();

      $weeks = [
        $option1,
        $option2,
        $option3,
      ];
    }
    else if( strtolower($season->league->league_url_name) === 'pga'){
      $weeks = Match::where('season_id', $season->season_id)
      ->where('scheduled_date_time', '>', format_date())
      ->get();
    }

    return $weeks;
  }

  private function subquery_for_ranged_weeks($allWeeks){

    // Get Min Max week and Min start and Max end date according to $allWeeks subquery
    return \DB::table(\DB::raw("({$allWeeks->toSql()}) as sub"))
    ->select(\DB::raw('CONCAT(\'NFL Weeks \', MIN("match_week"), \'-\', MAX("match_week")) as match_week, MIN("start_date_time") as start_date_time, MAX("end_date_time") as end_date_time'))
    ->mergeBindings($allWeeks->getQuery());
  }

  //get prizes for show prize h2h or multiplayer
  public function get_prizes(Request $request){
    $prizes = new Master_Prize;
    if($request->type === 'h2h' ){
      $prizes = $prizes->whereRaw("LOWER(prize_name) = 'top-1'");
    }else{
      $prizes = $prizes->whereRaw("LOWER(prize_name) IN ('top-3', 'top 30%')");
    }
    $prizes = $prizes->get();

    if($prizes->count() == 0){
      return response()->json([
        'response_code'=> 404,
        'service_name' => 'get_prizes',
        'global_error'=> 'No prizes found',
      ], 404);
    }

    return response()->json([
      'response_code'=> 200,
      'service_name' => 'get_prizes',
      'data' => $prizes,
      'message'=> 'Prizes found',
    ]);
  }

  //get touranament api for golf behalf start date or end date
  public function get_tournaments(Request $request){
    $tournaments = Match::whereBetween('scheduled_date_time', [$request->start_date, $request->end_date])
    ->where('season_id', $request->season_id)
    ->get();

    if($tournaments->count() == 0){
      return response()->json([
        'response_code'=> 404,
        'service_name' => 'get_tournaments',
        'global_error'=> 'No tournaments found',
      ], 404);
    }

    return response()->json([
      'response_code'=> 200,
      'service_name' => 'get_tournaments',
      'data' => $tournaments,
      'message'=> 'Tournaments found',
    ]);
  }

  //----------end create contest api//-------------------

	//get_all_contest_participants by contest_id
  public function contest_participants(Request $request)
  {
    $game_unique_id = $request->post('contest_id');
    $sub_contest_id = $request->post('sub_contest_id');

    $rules['contest_id'] = 'required';
    $rules['sub_contest_id'] = 'required';
    $customMessages = [
        'contest_id.required' => 'Contest Id is required',
        'sub_contest_id.required' => 'Sub Contest Id is required'
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' =>'get_contest_participants', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }
    //get contest participant by sub_contest_id
		$result = Linup_Master::with('user_detail')->where('sub_contest_id',$sub_contest_id)->OrderBy('total_score','DESC')->get();
		if (!empty($result)) {
      foreach ($result as &$val) 
      {
        switch ($val['status']) {
          case 1:
            $val['status_label'] = 'Joined';
            break;
          case 2:
            $val['status_label'] = 'Eliminate';
            break;
          case 3:
            $val['status_label'] = 'Winner';
            break;
        }
      }
		}
    if($result->count() == 0)
    {
      return response()->json([
        'service_name' =>'contest_participants',
        'global_error'=>'No Contest Participant Found',
      ],404);
    }
   		return response()->json([
        'service_name' =>'contest_participants',
        'message'=>'Get All Contest Participant',
        'data'=>$result
      ],200);
 	}

 	//get_all_user_team_by contest_user_prime
 	public function get_all_team_roaster(Request $request)
 	{
 	  $game_unique_id = $request->post('contest_id');
    $rules['contest_id'] = 'required';
    $customMessages = [
        'contest_id.required' => 'Contest Id is required'
    ];

    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'get_all_team_roaster', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }
    //get sub contest detail
    $sub_contest_detail = $this->common->get_sub_contest($game_unique_id);
    $sub_contest_id = $sub_contest_detail->sub_contest_id;

 		$result = Linup_Master::select('team_name','lineup_master_id as contest_user_prime_id')->where('sub_contest_id',$sub_contest_id)->get();
 		return response()->json(['service_name' => 'get_all_team','message'=>'','error'=>'','data'=>$result],200);
 	}

 	//get_contest_details by contest_unique_id
   public function get_contest_detail(Request $request)
 	{
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    $contest_unique_id = $request->post('contest_unique_id');
    $sub_contest_id = $request->post('sub_contest_id');
    $rules['contest_unique_id'] = 'required';
    $rules['sub_contest_id'] = 'required';
    $customMessages = [
        'contest_unique_id.required' => 'Contest Unique Id is required',
        'sub_contest_id.required'    => 'Sub Contest Id is required'
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json([
        'service_name' => 'get_contest_detail', 
        'message' => 'Validation Failed', 
        'global_error' => $validator->errors()
      ], 500);
    }

    $contest_detail = new Contest;
    $contest_detail = $contest_detail->with(['season.league','master_prize_payout','user_detail','sub_contests_details']);
    $contest_detail =$contest_detail->whereHas('sub_contests_details', function($q) use ($request){
      if($request->sub_contest_id)
      {
        $q->where('sub_contest_id', $request->sub_contest_id);
      }
    });

    $contest_detail = $contest_detail->where('contest_uid',$contest_unique_id);
    $contest_detail = $contest_detail->first();

    if($contest_detail['contest_uid'] != $contest_unique_id)
    {
      $error = '';
      return response()->json([
        'service_name' => 'get_contest_detail', 
        'message' => 'Invalid Contest Id', 
        'global_error' =>'Invalid Contest Id'
      ], 500);
    }

    // $user_contest_condition = array('sub_contest_id' => $sub_contest_id, 'user_id' => $user_id);
    // $user_contest_join_status = Linup_Master::where($user_contest_condition)->first();
    // if(!$user_contest_join_status)
    // {
    //   $error = 'User Contest Not Availabel.';
    //   return response()->json([
    //     'service_name' => 'get_contest_detail', 
    //     'message' => 'This Contest is not any Participant Availabel.', 
    //     'global_error' => $error
    //   ], 500);
    // }

    $prize_param = array($contest_detail['master_prize_payout']['composition'], $contest_detail['rewards'] == 0, $contest_detail['size']);
    $para = new Prize_evaluate($prize_param);
    $output['prize'] =  $para->evaluate_rewards();
 		return response()->json([
      'service_name' => 'get_contest_detail',
      'message'=>'Get Contest Details',
      'data'=>$contest_detail,
      'prizes'=>$output['prize']
    ],200);
 	}

}
