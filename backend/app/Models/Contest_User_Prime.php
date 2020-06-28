<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Contest_User_Prime extends Model
{
  protected $table = 'game.contest_user_primes';
  protected $primaryKey = 'contest_user_prime_id';
  public $timestamps = false;
  protected $fillable = [
	   "contest_id",
	   "user_id",
	   "team_name",
	   "total_score",
	   "season_id",
	   "status",
	   "joined_date",
  ];
  public function user_detail()
  {
    return $this->belongsTo('App\Models\User','user_id','user_id')->select('user_id','image');
  }

}