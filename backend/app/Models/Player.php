<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Player extends Model
{
    protected $table = 'game.players';
    protected $primaryKey = 'player_id';
    protected $fillable = [
        'team_id',
        'team_abbr',
        'player_id',
        'player_unique_id',
        'position_abbr',
        'jersey_number',
        'injury_status',
        'dob',
        'weight',
        'height',
        'status',
        'salary',
        'player_image',
        'first_name',
        'last_name',
        'full_name',
        'en_full_name',
        'position_type',
        'season_id'

	];

    public $timestamps = false;

    public function seasons_detail()
    {
        return $this->belongsTo('App\Models\Seasons','season_id','season_id')->select('season_start_date','season_end_date','season_year','league_id','season_id');
    }

    public function team_detail()
    {
        return $this->belongsTo('App\Models\Team','team_id','team_id');
    }
    public function feed_player_statistics()
    {
    	return $this->hasMany('App\Models\Feed_Player_Statistics','player_unique_id','player_unique_id');
    }
    public function match_player_scores()
    {
    	return $this->belongsTo('App\Models\Match_Player_Scores','player_unique_id','player_unique_id')->with('feed_player');
    }
    public function player_queue()
    {
        return $this->belongsTo('App\Models\Player_Queue','player_id','player_id');
    }
    //get lineup_detail
    public function lineup_detail()
    {
        return $this->belongsTo('App\Models\LineUp','player_id','player_id');
    }
}
