<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed_Match_Statistics extends Model
{
  protected $table = 'game.feed_match_statistics';
  protected $primaryKey = 'feed_match_statistics_id';
  protected $fillable = [
  	'feed_match_statistics_id',
  	'season_id',
  	'match_unique_id',
  	'match_type',
  	'home_stats',
  	'away_stats'
	];
  public $timestamps = false;
}
