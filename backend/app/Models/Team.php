<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'game.teams';
    protected $primaryKey = 'team_id';
    protected $fillable = [
    'team_id',
    'feed_team_id',
    'team_abbr',
    'team_name',
    'logo',
    'season_id'
	];
    public $timestamps = false;
}
