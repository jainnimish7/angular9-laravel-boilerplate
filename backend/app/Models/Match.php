<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
  protected $table = 'game.matches';
  protected $primaryKey = 'match_id';
  protected $fillable = [
  	'season_id',
  	'match_unique_id',
  	'match_type',
  	'week',
  	'feed_scheduled_date_time',
  	'scheduled_date_time',
  	'home',
  	'status',
  	'status_description',
  	'match_result',
  	'feed_week',
  	'match_id',
  	'end_date_time',
  	'tournament_name'

	];
  public $timestamps = false;
}
