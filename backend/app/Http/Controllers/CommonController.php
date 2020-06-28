<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\Common_model;
use App\Models\Leagues;
use App\Models\MasterGameStyle;
use App\Models\SubContest;
use App\Models\GameStyle;
use App\Models\Seasons;

class CommonController extends Controller
{
  public function country_list(Request $request)
  {
  	$all_country = Country::get_all_countries();

  	return response()->json(['response_code'=>200,'service_name'=>'country_list','data'=>$all_country,'message'=>'','error'=>'']);
  }

  public function country_list_code(Request $request)
  {
    $all_country = Country::get_all_countries_codes();

    return response()->json(['response_code'=>200,'service_name'=>'country_list','data'=>$all_country,'message'=>'','error'=>'']);
  }

  public function state_by_country(Request $request)
	{
		$country_abbr = $request->post('master_country_id');
		$data['state']  = State::select('*')->where('master_country_id',$country_abbr)->get();

		return response()->json(['response_code'=>200,'service_name'=>'state_by_country','data'=>array('state' => $data['state']),'message'=>'','error'=>'']);
	}

  //get_sub_contest data behalf contest
  public function get_sub_contest($contest_id)
  {
    $sub_contest = SubContest::where('contest_id',$contest_id)->first();
    return $sub_contest;
  }

  //get_sub_contest data behalf contest
  public function get_sub_contest_participants($contest_id, $sub_contest_id)
  {
    $sub_contest = SubContest::where('contest_id',$contest_id)->where('sub_contest_id',$sub_contest_id)->first();
    return $sub_contest;
  }

  public function get_all_league()
  {
      $all_league = Leagues::with(['season'])->select('league_id','league_abbr','league_name','logo')->where('status', 1)->get();
      return response()->json([
      'service_name' => 'get_all_league',
      'data' => $all_league,
      'message'=> 'Leagues Fetched',
    ],200);
  }

  public function get_game_style($season_id){

    $gameStyles = GameStyle::where('season_id', $season_id)
    ->where('status', 1)
    ->get();

    if( $gameStyles->count() > 0 ){

      return response()->json([
        'response_code'=> 200,
        'service_name' => 'get_game_styles',
        'data' => $gameStyles,
        'message'=> 'Pre Data Fetched',
      ]);

    }

    return response()->json([
      'response_code'=> 404,
      'service_name' => 'get_game_styles',
      'global_error'=> 'No game styles found',
    ], 404);
  }
  public function get_game_styles(){
    $gameStyles = MasterGameStyle::select('abbr','name')
    ->where('status', 1)
    ->groupBy('abbr', 'name')
    ->get();
      return response()->json([
      'service_name' => 'get_game_styles',
      'data' => $gameStyles,
      'message'=> 'Fetched all game styles',
    ],200);
  }

  public function get_sizes($seasonId, $gameStyleId){

    $season = Seasons::where('season_id', $seasonId)->first();
    $league = $season->league->league_url_name;
    $gameStyle = GameStyle::where('master_game_styles_id', $gameStyleId)->first()->abbr;

    $sizes = [];

    switch( $league ){
      case 'nfl':
        switch($gameStyle){
          case 'championship':
            $sizes = [
              [
                "name" => "Multiplayer",
                "abbr" => "multiplayer"
              ]
            ];
          break;
        }
      break;
      case 'pga':
        switch($gameStyle){
          case 'championship':
            $sizes = [
              [
                "name" => "Multiplayer",
                "abbr" => "multiplayer"
              ]
            ];
          break;
        }
      break;
    }

    return response()->json([
      'response_code'=> 200,
      'service_name' => 'get_size',
      'data' => $sizes,
      'message'=> 'Sizes Fetched',
    ]);

  }


}
