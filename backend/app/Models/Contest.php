<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
  protected $table = 'game.contests';

  protected $primaryKey = 'contest_id';

  protected $appends = ['total_user_joined'];

  const CREATED_AT = 'created_date';
  const UPDATED_AT = 'modified_date';

  protected $fillable = [
    "contest_id",
    "contest_uid",
    "contest_name",
    "start_date",
    "end_date",
    "season_id",
    "status",
    "entry_fees",
    "master_prize_id",
    "pick_duration",
    "user_id",
    "is_private",
    "size",
    "site_rake",
    "default_sub_contest_size",
    "projected_drafting_end_date",
    "playoff_date",
    "when_to_draft",
    "draft_speed",
    "draft_type",
    "lineup_style",
    "master_game_style_id",
    "prize_pool"
  ];

  public function getTotalUserJoinedAttribute(){
    $total_user_joined = $this->sub_contests->sum('total_user_joined');

    return $total_user_joined;
  }

  public function season()
  {
    return $this->belongsTo('App\Models\Seasons','season_id','season_id');
  }
  public function master_prize_payout()
  {
    return $this->belongsTo('App\Models\Master_Prize','master_prize_id','master_prize_id');
  }
  public function user_detail()
  {
    return $this->belongsTo('App\Models\User','user_id','user_id')->select('user_id','first_name','last_name');
  }
  public function sub_contests()
  {
    return $this->hasMany('App\Models\SubContest', 'contest_id', 'contest_id');
  }
  public function game_style(){
    return $this->belongsTo('App\Models\MasterGameStyle', 'master_game_style_id', 'master_game_styles_id');
  }

  public function sub_contests_details(){
  return $this->hasOne('App\Models\SubContest', 'contest_id', 'contest_id');
  }
}
