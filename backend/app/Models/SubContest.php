<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubContest extends Model
{
  protected $table = 'game.sub_contests';
  protected $primaryKey = 'sub_contest_id';
  protected $fillable = [
	  'sub_contest_id',
	  'sub_contest_uid',
	  'contest_id',
	  'total_user_joined',
	  'draft_date_time',
	  'created_date',
	  'modified_date',
	  'status',
	  'size',
	  'round'
	];
	public $timestamps = false;

	public function contest(){
		return $this->belongsTo('App\Models\Contest', 'contest_id', 'contest_id');
	}
	public function lineup_masters(){
		return $this->hasMany('App\Models\Linup_Master', 'sub_contest_id', 'sub_contest_id');
	}
}
