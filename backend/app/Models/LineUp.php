<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineUp extends Model
{
  protected $table = 'game.lineups';
  protected $primaryKey = 'lineup_id';
  protected $fillable = [
  'lineup_id',
  'lineup_master_id',
  'player_id',
  'round_number',
  'pick_number',
  'is_auto_picked',
  'created_date',
  'modified_date',
  'score'
	];
  public $timestamps = false;

  public function lineup_master(){
    return $this->belongsTo('App\Models\Linup_Master', 'lineup_master_id', 'lineup_master_id');
  }

  public function player(){
    return $this->belongsTo('App\Models\Player', 'player_id', 'player_id');
  }
}
