<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lineup_History extends Model
{
  protected $table = 'game.lineup_histories';
  protected $primaryKey = 'lineup_id';
  protected $fillable = [
  'lineup_historie_id',
  'lineup_id',
  'lineup_master_id',
  'lineup_master_sub_contest_id',
  'player_id',
  'round_number',
  'pick_number',
  'is_auto_picked',
  'score',
  'week',
  'created_date',
	];
  public $timestamps = false;
}
