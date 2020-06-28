<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master_Contest_Roster extends Model
{
  protected $table = 'game.master_contest_rosters';
  protected $primaryKey = 'master_contest_roster_id';
  protected $fillable = [
	  'master_contest_roster_id',
	  'season_id',
	  'position_abbr',
	  'min_size',
	  'sport_id',
	  'max_size',
	  'default_size',
	  'roster_sequence',
	  'allowed_positions',
	  'playing_xi_min',
	  'playing_xi_max'
	];
  public $timestamps = false;
}
