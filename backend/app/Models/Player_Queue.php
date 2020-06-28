<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player_Queue extends Model
{
   protected $table = 'game.player_queue';
  protected $primaryKey = 'player_queue_id';
  protected $fillable = [
  	'player_queue_id',
  	'lineup_master_id',
  	'player_id',
  	'sequence',
  	'created_date',
  	'modified_date',
  	'status'
	];
  public $timestamps = false;

  public function player_detail()
  {
  	return $this->belongsTo('App\Models\Player','player_id','player_id');
  }
}
