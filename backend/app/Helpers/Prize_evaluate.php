<?php
namespace App\Helpers;

class Prize_evaluate {

  public static $composition;

  public static $rewards;

  public static $size;

  public static $output = array(
                          'comprehensive_rewards' => NULL,
                          'specific_rewards'      => array()
                        );
  public static $comprehensive_rewards_text = " Top %d participants will get $ %s each ";


  public function __construct($param)
  {
    self::$composition = isset($param[0]) ? $param[0] : NULL ;
    self::$rewards     = isset($param[1]) ? $param[1] : NULL ;
    self::$size        = isset($param[2]) ? $param[2] : NULL ;

    if(!is_null(self::$composition)){
      $this->set_composition(self::$composition );
    }

    if(!is_null(self::$rewards)) {
      $this->set_rewards(self::$rewards);
    }

    if(!is_null(self::$size)) {
      $this->set_size(self::$size);
    }

  }

  protected function set_composition($composition) {

    if(is_string($composition)) {
      self::$composition  = json_decode($composition, true);
    } else if( is_array($composition)) {
      self::$composition = $composition;
    } else {
      echo "Invalid Composition, It should be either array Or JSON string ";
    }
  }

  protected function set_rewards($rewards) {
   self::$rewards = $rewards;
  }

  protected function set_size($size) {
    self::$size = $size;
  }

  public static function evaluate_rewards() {
    if(!is_array(self::$composition)) {
      return self::$output;
    }

    foreach(self::$composition as $val) {
      if(is_integer($val['max_place']) ) {
        // specific
       self::prepare_specific_rewards_list( $val['max_place'], $val['min_place'] , $val['value']);
      }  else {
         self::prepare_comprehensive_rewards_text($val['max_place']);
      }
    }

    return self::$output;

  }

  protected static function prepare_comprehensive_rewards_text( $max ) {
    list($str, $divider) =  explode('/', $max);

    $divider = ($divider == 0) ? 1 : $divider;

    $max_place = intval( self::$size / $divider );
    $prize_each_place = number_format( self::$rewards/$max_place, 2 ) ;

    self::$output['comprehensive_rewards'] = sprintf(self::$comprehensive_rewards_text, self::ordinal( $max_place ) ,$prize_each_place );

  }

  protected static function prepare_specific_rewards_list($max, $min, $percent) {
    $amount = ($percent *  self::$rewards ) / 100 ;
    $amount = number_format($amount, 2);
    $place = self::ordinal($max);

    if($max != $min ) {
      $place = self::ordinal($min). ' - '. self::ordinal($max) ;
    }

    $single_place = array( 'place' => $place, 'amount' => $amount );

   return self::$output['specific_rewards'][] = $single_place;
  }

  protected static function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
      return $number. 'th';
    else
      return $number. $ends[$number % 10];
  }

}
