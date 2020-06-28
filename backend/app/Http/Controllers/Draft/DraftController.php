<?php

namespace App\Http\Controllers\Draft;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

# Models
use App\Models\Master_Contest_Roster;
use App\Models\SubContest;
use App\Models\Linup_Master;

# Libraries and Helpers
use Auth;

class DraftController extends Controller
{
  public function get_roaster_positions($subContestUniqueId){

    if(!$subContest = SubContest::where('sub_contest_uid', $subContestUniqueId)
    ->first()){
      return response()->json([
        'service_name' => 'get_roaster_positions',
        'message'=> 'No sub contest found',
      ], 404);
    }

    $contestRoasters = Master_Contest_Roster::where('season_id', $subContest->contest->season_id)
    ->where('lineup_style', $subContest->contest->lineup_style)
    ->get();

    foreach( $contestRoasters as $contestRoaster  ){
      $contestRoaster->filled = $subContest->lineup_masters()
      ->where('user_id', Auth::user()->user_id)
      ->first()->lineups()
      ->where('position_abbr', $contestRoaster->position_abbr)
      ->count();
    }

    return response()->json([
      'service_name' => 'get_roaster_positions',
      'data' => $contestRoasters,
      'message'=> 'Contest Roasters Positions Found',
    ], 200);

  }

  public function get_draft_history($subContestUniqueId){
    if(!$subContest = SubContest::where('sub_contest_uid', $subContestUniqueId)
    ->first()){
      return response()->json([
        'service_name' => 'get_draft_history',
        'message'=> 'No sub contest found',
      ], 404);
    }

    $lineups = $subContest->lineup_masters()
    ->with('lineups')
    ->whereHas('lineups', function($wh){
      $wh->whereNotNull('player_id');
    })
    ->get();

    return response()->json([
      'service_name' => 'get_draft_history',
      'data' => $lineups,
      'message'=> 'Lineups Found',
    ], 200);
  }

  public function get_lineup_master($lineupMasterId){

    $lineupMaster = Linup_Master::where('lineup_master_id', $lineupMasterId)
    ->with('lineups.player', 'user')
    ->first();

    if(!$lineupMaster){
      return response()->json([
        'service_name' => 'get_lineup_master',
        'message'=> 'No linup master found',
      ], 404);
    }

    return response()->json([
      'service_name' => 'get_lineup_master',
      'data' => $lineupMaster,
      'message'=> 'Lineup Master Found',
    ], 200);

  }
}
