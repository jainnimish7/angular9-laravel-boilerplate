<?php

namespace App\Http\Controllers\Draft;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

# Models
use App\Models\LineUp;
use App\Models\Linup_Master;
use App\Models\Player_Queue;
use App\Models\Player;
use App\Models\Master_Contest_Roster;

# Helpers and Libraries
use Auth;

class AutoPickPlayerController extends Controller
{
  public function index(Request $request){

    $this->user = Auth::user();

    # Pull out lineup
    $lineup = LineUp::where('lineup_id', $request->lineup_id)->whereHas('lineup_master', function($wi){
      $wi->where('user_id', $this->user->user_id);
    })->first();

    if( !$lineup ){

      return response()->json([
        'response_code'=> 404,
        'service_name' => 'index',
        'global_error'=> 'No lineup found',
      ], 404);

    }else if( $lineup->player_id ){
      return response()->json([
        'response_code'=> 401,
        'service_name' => 'index',
        'global_error'=> 'This position is already filled',
      ], 401);
    }

    # Pull out players queue of loggedin user who matches position give and are open for drafting
    $userPlayerQueue = $lineup->lineup_master->player_queue()
    ->where('status','0')
    ->orderBy('sequence', 'ASC')
    ->get();

    $subContest = $lineup->lineup_master->sub_contest;

    # Pull out all lineup masters belongs to this contest
    $lineupMasters = $subContest->lineup_masters()
    ->where('lineup_master_id', '!=', $lineup->lineup_master->lineup_master_id)
    ->get();

    foreach( $userPlayerQueue as $player){

      # Pull out contest roaster belongs to contest's lineup style and queue's abbr
      $contestRoaster = Master_Contest_Roster::where('season_id', $subContest->contest->season_id)
      ->where('position_abbr', $player->position_abbr)
      ->where('lineup_style', $subContest->contest->lineup_style)
      ->first();

      # Pull out count of position's already filled in user's lineup
      $positionsAlreadyTaken = LineUp::where('lineup_master_id', $lineup->lineup_master_id)
      ->where('position_abbr', $player->position_abbr)
      ->count();

      if( $contestRoaster->max_size == $positionsAlreadyTaken ){
        # all positions are already filled, update the status of this player queue and move on
        $player->status = 1;
        $player->save();
        continue;
      }

      foreach($lineupMasters as $lineupMaster){
        if($isPlayerAlreadyTaken = $lineupMaster->lineups()
        ->where('player_id', $player->player_id)
        ->first()){
          # Change Status in queue as already picked and continue if player is already been taken
          $player->status = 1;
          $player->save();
          continue;
        }

        # Update lineup if player is available
        $lineup->player_id = $player->player_id;
        $lineup->is_auto_picked = 1;
        $lineup->position_abbr = $player->position_abbr;
        $lineup->save();

        # Change Status in queue as already picked
        $player->status = 1;
        $player->save();

        return response()->json([
          'service_name' => 'index',
          'data' => $player->player_detail,
          'message'=> 'Player Found',
        ], 200);

      }

    }

    return $this->setPlayerRandomly($subContest, $lineup);
  }

  private function setPlayerRandomly($subContest, $lineup){
    $takenPlayersIds = [];

    // Loop through and pull out player ids which are not been taken in this sub contest
    foreach( $subContest->lineup_masters as $lineupMaster ){
      foreach( $lineupMaster->lineups as $lup ){
        if( $lup->player_id){
          $takenPlayersIds[] = $lup->player_id;
        }
      }
    }

    # Pull out all roaster positions for season lineup style wise
    $contestRoasters = Master_Contest_Roster::where('season_id', $subContest->contest->season_id)
    ->where('lineup_style', $subContest->contest->lineup_style)
    ->get();

    $player = null;

    foreach( $contestRoasters as $cR){

      # Pull out count of position's already filled in user's lineup
      $positionsAlreadyTaken = LineUp::where('lineup_master_id', $lineup->lineup_master_id)
      ->where('position_abbr', $cR->position_abbr)
      ->count();

      if( $cR->max_size == $positionsAlreadyTaken ){
        continue;
      }

      // Get a random player
      $player = Player::whereNotIn('player_id', $takenPlayersIds)
      ->where('season_id', $subContest->contest->season_id)
      ->where('position_abbr', $cR->position_abbr)
      ->inRandomOrder()
      ->first();

      if(!$player){
        return response()->json([
          'service_name' => 'index',
          'data' => $player,
          'message'=> 'All Players are taken!',
        ], 404);
      }

      # Update lineup if player is available
      $lineup->player_id = $player->player_id;
      $lineup->position_abbr = $player->position_abbr;
      $lineup->is_auto_picked = 1;
      $lineup->save();

    }

    if( $player ){
      return response()->json([
        'service_name' => 'index',
        'data' => $player,
        'message'=> 'Player Found',
      ], 200);
    }else{
      return response()->json([
        'service_name' => 'index',
        'message'=> 'Lineup is filled up!',
      ], 404);
    }
  }

}
