<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\Master_Contest_Roster;
// use App\Models\Contest_User_Prime;
use App\Models\Linup_Master;
use App\Models\Player;
use App\Models\Team;
use App\Models\Seasons;
use App\Models\Player_Queue;
use App\Models\Leagues;
use App\Models\LineUp;
use App\Http\Controllers\CommonController;
// use App\Models\Lineup_Master_Sub_Contest;
use App\Models\SubContest;
use Auth;
use Validator;
use DB;

class LineUpController extends Controller
{
  //define here constructor
  protected $common;
  public function __construct(CommonController $common)
  {
    $this->common = $common;
  }

  //get line up details
  public function get_lineup_detail(Request $request)
  {
   	$this->user = Auth::user();
    $user_id = $this->user->user_id;
    $rules['contest_unique_id'] = 'required';
    $customMessages = [
        'contest_unique_id.required' => 'Contest Unique Id is required',
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'get_lineup_detail', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }
    //change contest_id to contest_uid
    $contest_unique_id = $request->post('contest_unique_id');
    $contest_detail = Contest::with('season.league')->where('contest_uid',$contest_unique_id)->first();
     //get_sub_contest data behalf contest
    $sub_contest = $this->common->get_sub_contest($contest_detail['contest_id']);
    $sub_contest_id = $sub_contest->sub_contest_id;

    if(!$contest_detail)
    {
      $error = 'Invalid Contest Unique Id';
      return response()->json(['service_name' => 'get_lineup_detail', 'message' =>'', 'error' =>'', 'global_error' => $error], 500);
    }
    $league_detail = $contest_detail['season']['league'];
    //get_all_line_up_position according to season
   	$lineup_position = $this->get_all_position($contest_detail['season_id']);
    $league_info = Leagues::where('league_id',$league_detail['league_id'])->orWhere('league_year',$league_detail['league_year'])->first();
    $user_teams = Linup_Master::where('sub_contest_id',$sub_contest_id)->where('user_id',$user_id)->get();
    $teams = $this->get_all_teams($contest_detail['season_id']);
   	$data['lineup_position'] = $lineup_position;
   	$data['teams'] = $teams;
    $data['user_teams'] = $user_teams;
   	$data['league_info'] = $league_info;
   	return response()->json(['service_name' => 'get_lineup_detail','message'=>'','error'=>'','data'=>$data],200);
  }

  //get_all_roaster_position by season_id
  public function get_all_position($season_id)
  {
		$result = array();
    $result =Master_Contest_Roster::select('position_abbr as position','min_size','max_size','default_size','roster_sequence','allowed_positions','playing_xi_min', 'playing_xi_max')
    ->where('season_id', $season_id)
    ->whereNotNull('position_abbr')
    ->orderBy('roster_sequence', 'ASC')
    ->get();
    return $result;
  }

  //get all team by season_id
  private function get_all_teams($season_id)
  {
  	$teams = Team::select('team_id', 'team_abbr', 'team_name')
            ->where('season_id',$season_id)
            ->groupBy('team_name','team_id','team_abbr')
            ->orderBy('team_name')
            ->get();
  	return $teams;
  }

  //api Default Player Rankings - player queue -do not draft
  public function get_available_players(Request $request)
  {
    $contest_unique_id = $request->post('contest_uid');
    $sub_contest_id = $request->post('sub_contest_id');

    $rules['contest_uid'] = 'required';
    $rules['sub_contest_id'] = 'required';
    $customMessages = [
        'contest_uid.required' => 'Contest Unique Id is required',
        'sub_contest_id.required' => 'Sub Contest Id is required'
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json([
        'service_name' => 'get_available_players',
        'message' => '', 
        'global_error' => $error
      ], 500);
    }

    $contest_detail = Contest::select('season_id')->where('contest_uid',$contest_unique_id)->first();
		$season_id = $contest_detail['season_id'];
    //get player queue
    $data['player_queue'] = $this->get_player_queue();
    //get player do not draft
		$data['do_not_draft'] = $this->get_player_do_not_draft();
    //get player id exit in player queue and do not draft queue
    $player_exit_in_queue = array_column($data['player_queue'], 'player_id');
    $player_exit_in_draft = array_column($data['do_not_draft'], 'player_id');
    //get default player
    $data['default_player'] = $this->get_default_player($season_id,$player_exit_in_queue,$player_exit_in_draft);
    return response()->json([
      'service_name' =>'get_available_players',
      'message'=>'',
      'data'=>$data
    ],200);

  }

  //get player exits in player queue
  private function get_player_queue()
  {
    $get_all_player = Player::select('player_id')->where('status',1)->get()->toArray();
    $get_all_player_id = array_column($get_all_player, 'player_id');
    $all_player_queue  = new Player;
    $all_player_queue  = $all_player_queue->with(['team_detail','player_queue']);
    $all_player_queue =  $all_player_queue->whereHas('player_queue', function($q) use ($get_all_player_id){
        $q->whereIn('player_id', $get_all_player_id);
        $q->where('status', 2);
    });
    $all_player_queue = $all_player_queue->get()->toArray();
    return $all_player_queue;
  }

  //get player exit in do_not_draft
  private function get_player_do_not_draft()
  {
    $get_all_player = Player::select('player_id')->where('status',1)->get()->toArray();
    $get_all_player_id = array_column($get_all_player, 'player_id');
    $all_player_do_not_draft  = new Player;
    $all_player_do_not_draft  = $all_player_do_not_draft->with(['team_detail','player_queue']);
    $all_player_do_not_draft =  $all_player_do_not_draft->whereHas('player_queue', function($q) use ($get_all_player_id){
        $q->whereIn('player_id', $get_all_player_id);
        $q->where('status', 3);
    });
    $all_player_do_not_draft = $all_player_do_not_draft->get()->toArray();
    return $all_player_do_not_draft;
  }

  //get_all_default player according to contest session_id
  private function get_default_player($season_id,$player_queue_id,$player_draft_id)
  {
  	$all_default_player  = new Player;
  	$all_default_player = $all_default_player->select('player_id','player_unique_id','full_name','en_full_name','first_name','last_name','game.players.team_id','position_abbr as position','season_id');
    $all_default_player = $all_default_player->with('team_detail');
  	$all_default_player = $all_default_player->whereDoesntHave('player_queue', function ($query)use ($player_queue_id,$player_draft_id) {
    		$query->whereIn('player_id', $player_queue_id);
        $query->orWhereIn('player_id', $player_draft_id);
		});
		$all_default_player = $all_default_player->where('season_id',$season_id);
    $all_default_player = $all_default_player->where('status',1);
    $all_default_player = $all_default_player->orderBy('player_id','ASC');
  	$all_default_player = $all_default_player->get();
  	return $all_default_player;
  }

  //add player in player queue
  public function prepare_add_to_player_queue(Request $request)
  {
    $post_data = $request->post();
    $pre_player = $request->post('pre_player');
    $do_not_draft = $request->post('do_not_draft');
    $this->user = Auth::user();
    $user_id = $this->user->user_id;

    //interction of pre_player and do not draft
    $result_intersect = $this->intersect($pre_player,$do_not_draft);
    if($result_intersect)
    {
      $error = 'You added player already picked in another queue';
      return response()->json(['service_name' => 'prepare_add_to_player_queue', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }

    //check player already queue set //then delete queue and re insert again
    //get sub contest 
    $sub_contest = $this->common->get_sub_contest($request->contest_id);
    $sub_contest_id = $sub_contest->sub_contest_id;

    $check_player_already_queue = $this->check_is_player_queue($sub_contest_id,$user_id);
    if($check_player_already_queue)
    {
      $deleted_player_queue = Player_Queue::where('lineup_master_id',$check_player_already_queue->lineup_master_id)->delete();
    }
    if(!$result_intersect)
    {
      foreach ($pre_player as $key => $value) {
        // check player id is valid or not
        $check_player_is_valid = Player::select('player_unique_id')->where('player_id', $value)->first();
        if($check_player_is_valid)
        {
            $pre_player_data_arr = array(
              'created_date' => time_machine(),
              'modified_date'=>time_machine(),
              'sequence' => $key,
              'player_id'=>$value,
              'lineup_master_id' =>$check_player_already_queue->lineup_master_id,
              'status' => 2,
            );
            $insert_player_queue_pre = Player_Queue::insert($pre_player_data_arr);
        }
      }
      foreach ($do_not_draft as $key => $value) {
        $check_player_is_valid = Player::select('player_unique_id')->where('player_id', $value)->first();
         if($check_player_is_valid)
        {
          $do_not_draft_data_arr = array(
            'created_date'=>time_machine(),
            'modified_date'=>time_machine(),
            'sequence' => $key,
            'player_id'=>$value,
            'lineup_master_id' =>$check_player_already_queue->lineup_master_id,
            'status' => 3,
          );
          $insert_player_queue_do_not = Player_Queue::insert($do_not_draft_data_arr);
        }
      }
      return response()->json(['service_name' =>'prepare_add_to_player_queue','message'=>'Player Added Successfully','error'=>'','data'=>''],200);
    }
    else
    {
      $error = 'Player already in queue';
      return response()->json(['service_name' => 'prepare_add_to_player_queue', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }
  }

  // PHP function to illustrate the use of array_intersect()
  private function intersect($array1, $array2)
  {
      $result = array_intersect($array1, $array2);
      return($result);
  }

  public function check_is_player_queue($sub_contest_id, $user_id)
  {
    $check_user_prime = Linup_Master::select('lineup_master_id')->where('sub_contest_id',$sub_contest_id)->where('user_id',$user_id)->first();
    return $check_user_prime;
  }

  //get_player_drafted_in_player_queue
  public function get_player_drafted_queue(Request $request)
  {
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    $rules['sub_contest_id'] = 'required';
    $customMessages = [
        'sub_contest_id.required' => 'Sub Contest Id is required'
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json([
        'service_name' => 'get_player_drafted_queue',
        'message' => 'Validation Failed',
        'global_error' => $validator->errors()
      ], 500);
    }

    $sub_contest_id = $request->post('sub_contest_id');
    $contest_user_prime_id = $this->check_is_player_queue($sub_contest_id, $user_id);
    $contest_user_prime_id = $contest_user_prime_id['lineup_master_id'];

    $draft_queue = new Player;
    $draft_queue = $draft_queue->select('player_id','player_unique_id','full_name','first_name','last_name','en_full_name','team_id','position_abbr as position','season_id');
    $draft_queue = $draft_queue->with('player_queue','team_detail:team_id,team_name,team_abbr');
    //Filter (Relationship) Filter
    $draft_queue = $draft_queue->whereHas('player_queue', function($q) use ($request,$contest_user_prime_id){
        $q->where('status', 2);
        $q->where('lineup_master_id', $contest_user_prime_id);     
    });
    $draft_queue = $draft_queue->get();

    //get don't drafted player
    $dont_draft_player = Player_Queue::select('player_id')->where('lineup_master_id',$contest_user_prime_id)->where('status',3)->get();

    return response()->json([
      'service_name'=>'get_player_drafted_queue',
      'message'=>'Get Player Queue',
      'data'=>$draft_queue,
      'do_not_draft_player'=>$dont_draft_player
    ],200);
  }

  //save player_drafted
  public function save_queue_player(Request $request)
  {
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    $rules['contest_unique_id'] = 'required';
    $rules['sub_contest_id'] = 'required';
    $customMessages = [
        'contest_unique_id.required' => 'Contest Unique Id is required',
        'sub_contest_id.required'    => 'Sub Contest Id is required'
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      return response()->json([
        'service_name' => 'save_queue_player',
        'message' => '', 
        'global_error' =>$validator->errors()
      ], 500);
    }
    //change contest_id to contest_uid
    $contest_unique_id = $request->post('contest_unique_id');
    $sub_contest_id = $request->post('sub_contest_id');
    $darfed_queue_arr = $request->post('draft_queue_arr');

    $contest_detail = Contest::select('contest_id')->where('contest_uid',$contest_unique_id)->first();
    if(!$contest_detail)
    {
      $error = 'Invalid Contest Unique Id';
      return response()->json([
        'service_name' => 'save_queue_player',
        'message' =>'', 
        'error' =>'', 
        'global_error' => $error
      ], 500);
    }
    //this query is check_queue_exit
    $check_queue_exit =  Linup_Master::select('lineup_master_id')->where('sub_contest_id',$sub_contest_id)->where('user_id',$user_id)->first();

    if(!$check_queue_exit)
    {
      $error = 'Invalid Sub Contest Id';
      return response()->json([
        'service_name' => 'save_queue_player',
        'message' =>'', 
        'error' =>'', 
        'global_error' => $error
      ], 500);
    }

    //this query is check player already in another queue ('do not draft')
    $already_another_queue = Player_Queue::where('lineup_master_id',$check_queue_exit->lineup_master_id)->whereIn('player_id',$darfed_queue_arr)->where('status',3)->get();

    if(count($already_another_queue) > 0)
    {
      $error = 'Player already exist in do not draft.Choose another one.';
      return response()->json([
        'service_name' => 'get_player_drafted_queue', 
        'global_error' => $error
      ], 500);
    }

    //check queue exit - if queue exit delete and re insert again
    if($check_queue_exit)
    {
      $deleted_queue = Player_Queue::where('lineup_master_id',$check_queue_exit->lineup_master_id)->where('status',2)->delete();
    }
    //insert in player queue 
    foreach ($darfed_queue_arr as $key => $value) {
      // check player id is valid or not
      $check_player_is_valid = Player::select('player_unique_id')->where('player_id', $value)->first();
      if($check_player_is_valid)
      {
          $drafted_player_data_arr = array(
            'created_date' => time_machine(),
            'modified_date'=>time_machine(),
            'sequence' => $key,
            'player_id'=>$value,
            'lineup_master_id' =>$check_queue_exit->lineup_master_id,
            'status' => 2,
          );
          $insert_player_queue_draft = Player_Queue::insert($drafted_player_data_arr);
      }
    }
    return response()->json([
      'service_name' =>'save_player_drafted_queue',
      'message'=>'Your queue has been updated successfully.',
    ],200);
  }

  //get_team_roster
  public function get_users_roster(Request $request)
  {
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    //change contest_id to contest_uid
    $contest_unique_id = $request->post('contest_unique_id');
    $contest_detail = Contest::select('contest_id')->where('contest_uid',$contest_unique_id)->first();
     //get sub contest 
    $sub_contest = $this->common->get_sub_contest($contest_detail['contest_id']);
    $sub_contest_id = $sub_contest->sub_contest_id;

    $lineup_master = Linup_Master::select('lineup_master_id')->where('sub_contest_id',$sub_contest_id)->where('user_id',$user_id)->first();

    if($lineup_master != null)
    {
      $lineup_master_id = $lineup_master->lineup_master_id;
      $roster_detail = new Player;
      $roster_detail = $roster_detail->select('player_id','player_unique_id','full_name','first_name','last_name','en_full_name','team_id','position_abbr as position','season_id');
      $roster_detail = $roster_detail->with('lineup_detail','team_detail:team_id,team_name,team_abbr');
      //Filter (Relationship) Filter
      $roster_detail = $roster_detail->whereHas('lineup_detail', function($q) use ($lineup_master_id){
          $q->where('lineup_master_id',$lineup_master_id);     
      });
      $roster_detail = $roster_detail->get();
      return response()->json(['service_name'=>'get_users_roster','message'=>'','error'=>'','data'=>$roster_detail],200);
    }
    else
    {
      $error = 'User Contest Not availabel.';
      return response()->json(['service_name' => 'get_users_roster', 'message' =>'', 'error' =>'', 'global_error' => $error], 500);
    }
  }

  public function add_draft_queue(Request $request)
  {
    $this->user = Auth::user();
    $user_id = $this->user->user_id;
    $lineup_id = $request->lineup_id;
    $player_id = $request->player_id;
    $rules['lineup_id'] = 'required';
    $rules['player_id'] = 'required';
    $customMessages = [
        'lineup_id.required' => 'Lineup Id is required',
        'player_id.required' => 'Player Id is required',
    ];
    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'add_draft_queue', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }

    $updateLineupPlayer = LineUp::where('lineup_id',$lineup_id)->update(['player_id' => $player_id ]);
    if($updateLineupPlayer  > 0){
      return response()->json(['service_name' =>'add_draft_queue','message'=>'Your LineUp player updated successfully.'],200); 
    }else{
      return response()->json(['service_name' => 'add_draft_queue', 'message' => 'Some wrong in updating lineup player'],500);      
    }
    
  }

  //get draft history - line_up histroy
  public function get_lineup_data()
  {
    
  }


  //api lineup sub contest data
  public function get_lineups($sub_contest_id)
  {
    $lineup = Linup_Master::leftJoin('game.lineups', 'lineups.lineup_master_id', '=', 'lineup_masters.lineup_master_id')->where('sub_contest_id', $sub_contest_id)->whereNotNull('game.lineups.lineup_master_id')->orderBy('round_number', 'asc')->orderBy('pick_number', 'asc')->get();
    
    return response()->json(['response_code'=> 200,'service_name' => 'get_lineups','message'=> 'Get lineup successfully', 'data' => $lineup]);
    
  }

}
