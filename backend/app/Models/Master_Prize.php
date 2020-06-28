<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master_Prize extends Model
{
  protected $table = 'game.master_prizes';
  protected $primaryKey = 'master_prize_id';
  protected $fillable = [
  	'master_prize_id',
  	'prize_name',
  	'composition',
  	'status',
  	'prize_sequence',
  	'created_date'
	];
  public $timestamps = false;
}
