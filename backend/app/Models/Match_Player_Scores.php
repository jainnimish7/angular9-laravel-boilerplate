<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Match_Player_Scores extends Model
{
  protected $table = 'game.match_player_scores';
  protected $primaryKey = '';
  protected $fillable = [
  	'player_unique_id',
  	'score',
  	'break_down',
  	'feed_match_statistics_id',
  	'team_id'
	];
  public $timestamps = false;
 	public function feed_player()
	{
		return $this->belongsTo('App\Models\Feed_Player_Statistics','feed_match_statistics_id','feed_match_statistics_id');
	}
}
