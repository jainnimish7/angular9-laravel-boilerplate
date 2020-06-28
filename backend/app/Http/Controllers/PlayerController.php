<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Feed_Player_Statistics;
use App\Models\LineUp;
use Validator;

class PlayerController extends Controller
{
  //get_all_player_for_drafting
  public function get_players_list(Request $request)
  {
  	$all_players = new Player;
    //select column
    $all_players = $all_players->select('player_id','player_unique_id','full_name','first_name','last_name','en_full_name','team_id','position_abbr as position','season_id','injury_status','adp_rank');
    //with relation functions
  	$all_players = $all_players->with('seasons_detail.league:league_id,league_abbr','team_detail:team_id,team_name,team_abbr');
    //match player_score
		$all_players = $all_players->with(['match_player_scores' => function($query){
      $query->selectRaw('sum(score) as fantacy_points ');
		}]);
  	//team_id filter
		$all_players = $all_players->whereHas('team_detail', function($q) use ($request){
			if(isset($request->team_id) && $request->team_id > -1)
		  {
		   	$all_players = $q->where('team_id', $request->team_id);
		  }
  	});
  	//position filter
  	if (isset($request->position) && $request->position != "All" && $request->position != '')
  	{
	    $all_players = $all_players->where('position_abbr', $request->position);
	  }
 		// Partial Keyword Search Filter with player name
    if($request->keyword != "" ){
      $all_players= $all_players->where('full_name', 'ilike', '%'.$request->keyword.'%');
      $all_players= $all_players->orWhereRaw('en_full_name', 'ilike', '%'.$request->keyword.'%');
    }
    $all_players = $all_players->where('status',1);
   // $all_players = $all_players->orderBy('full_name','ASC');
    $all_players = $all_players->orderBy('player_id','ASC');
    $all_players = $all_players->get();
    

    

		return response()->json(['service_name' => 'get_players_list','message'=>'','error'=>'','data'=>$all_players],200);
  }

  //get_player_info and player stats
  public function player_card_details(Request $request)
  {
  	$player_unique_id = $request->post('player_unique_id');
  	$league_id = $request->post('league_id');
    $rules['player_unique_id'] = 'required';
    $rules['league_id'] = 'required';

    $customMessages = [
      'player_unique_id.required' => 'Player Unique Id is required',
      'league_id.required' => 'League Id is required',
    ];

    $validator = Validator::make($request->all(), $rules,$customMessages);
    if ($validator->fails()) {
      $error = $validator->errors();
      $error_all = $validator->messages()->all();
      $message = $error_all[array_keys($error_all)[0]];
      return response()->json(['service_name' => 'player_card_details', 'message' => '', 'error' =>'', 'global_error' => $error], 500);
    }

  	$info = $this->player_info($player_unique_id, $league_id);
  	$stats = $this->player_stats($player_unique_id, $league_id);
    $player_average_pick = $this->player_pick_average($player_unique_id);
    $data = array(
      'info' => $info,
      'statistics' => $stats,
      'player_average_pick'=> $player_average_pick
    );
  	return response()->json(['service_name' => 'player_card_details','message'=>'','error'=>'','data'=>$data],200);
  }

  private function player_info($player_unique_id, $league_id)
  {
  	$player_info = new Player;
  	$player_info = $player_info->select('player_id','first_name','last_name','full_name','team_id','team_abbr','position_abbr as position','jersey_number','player_image');
  	$player_info = $player_info->with('team_detail:team_id,team_name,team_abbr');
  	$player_info = $player_info->where('player_unique_id', $player_unique_id);
  	//league_id filter
		$player_info = $player_info->whereHas('seasons_detail.league', function($q) use ($league_id){
		  if(isset($league_id) && $league_id > -1)
		  {
		   	$player_info = $q->where('league_id', $league_id);
		  }
  	});
  	$player_info = $player_info->first();
  	return $player_info;
  }

  private function player_stats($player_unique_id, $league_id)
  {
    $player_statistics = new Feed_Player_Statistics;
    $player_statistics = $player_statistics->select('*')->first();
    return $player_statistics;
  }

  private function player_pick_average($player_unique_id)
  {
    $player = new Player;
    $player = $player->where('player_unique_id', $player_unique_id)->first();
    if($player){
        $playerLineup = LineUp::select('pick_number')->where('player_id', '=', $player->player_id)->get();
        if($playerLineup){
          foreach ($playerLineup as $res) {
              $data_array[] = $res->pick_number; 
              $resultdata[] = array(
                  'pick_number' => $res->pick_number
              );
          }  
          if (!empty($resultdata)) {
              //Calculate the average.
              $average = array_sum($data_array) / count($data_array);
              return $playerAverage = round($average,1);
          }else{
              return $playerAverage = 0;
          }
      }
      }
  }

  private function player_drafting($player_unique_id){
    $player = new Player;
    $player = $player->where('player_unique_id', $player_unique_id)->first();
    if($player){
      
    }
  }  


}
