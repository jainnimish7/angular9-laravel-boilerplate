<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lineup_Master_Sub_Contest extends Model
{
  protected $table = 'game.lineup_master_sub_contest';
  protected $primaryKey = 'lineup_master_sub_contest_id';
  protected $fillable = [
  'lineup_master_sub_contest_id',
  'lineup_master_id',
  'sub_contest_id',
  'is_commisioner',
  'pick_order',
  'reverse_pick_order',
  'total_score'
	];
  public $timestamps = false;
}
