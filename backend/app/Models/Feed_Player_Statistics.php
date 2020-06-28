<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed_Player_Statistics extends Model
{
 	protected $table = 'game.feed_player_statistics';
  protected $primaryKey = 'feed_match_statistics_id';
  protected $fillable = [
  	'feed_match_statistics_id',
  	'player_unique_id',
  	'position_abbr',
  	'assists',
  	'modified_date',
  	'team_id',
  	'goal_minutes',
  	'shots_total',
  	'shots_on_goal',
  	'goals_conceded',
  	'offsides',
  	'fouls_drawn',
  	'fouls_commited',
  	'tackles',
  	'blocks',
  	'total_crosses',
  	'acc_crosses',
  	'interceptions',
  	'clearances',
  	'is_captain',
  	'is_subst',
  	'yellow_cards',
  	'dispossesed',
  	'saves',
  	'saves_inside_box',
  	'duels_total',
  	'duels_won',
  	'dribble_attempts',
  	'dribble_succ',
  	'dribbled_past',
  	'red_cards',
  	'pen_score',
  	'pen_miss',
  	'pen_save',
  	'pen_committed',
  	'pen_won',
  	'hit_woodwork',
  	'passes',
  	'passes_acc',
  	'key_passes',
  	'minutes_played',
  	'rating',
  	'goals',
  	'own_goals'
	];
  public $timestamps = false;
}
