<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linup_Master extends Model
{
  protected $table = 'game.lineup_masters';
  protected $primaryKey = 'lineup_master_id';
  protected $fillable = [
    'lineup_master_id',
    'user_id',
    'sub_contest_id',
    'total_score',
    'status',
    'team_name',
    'joined_date',
    'pick_order',
    'reverse_pick_order'
	];
  public $timestamps = false;

  public function user_detail()
  {
    return $this->belongsTo('App\Models\User','user_id','user_id')->select('user_id','image');
  }

  public function user()
  {
    return $this->belongsTo('App\Models\User','user_id','user_id');
  }

  public function player_queue(){
    return $this->hasMany('App\Models\Player_Queue', 'lineup_master_id', 'lineup_master_id');
  }

  public function sub_contest(){
    return $this->belongsTo('App\Models\SubContest', 'sub_contest_id', 'sub_contest_id');
  }

  public function lineups(){
    return $this->hasMany('App\Models\LineUp', 'lineup_master_id', 'lineup_master_id');
  }
}
