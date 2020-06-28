<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use DB;
class Common_model extends Model
{
  public static function get_all_countries(){
  	$countries = Country::OrderBy('country_name')->get();
  	return $countries;
  }
  public static function get_all_countries_codes(){
  	$countries = Country::select(DB::raw("CONCAT('(',dial_code,')') AS dial_code"),'country_name')->OrderBy('country_name')->get();
  	return $countries;
  }    
}
