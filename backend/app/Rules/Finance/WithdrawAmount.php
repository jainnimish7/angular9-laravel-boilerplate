<?php

namespace App\Rules\Finance;

use Illuminate\Contracts\Validation\Rule;

# Helpers & Libraries
use Config;
use Auth;

class WithdrawAmount implements Rule
{
  /**
   * Create a new rule instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->message = '';
  }

  /**
   * Determine if the validation rule passes.
   *
   * @param  string  $attribute
   * @param  mixed  $value
   * @return bool
   */
  public function passes($attribute, $value)
  {

    $user = Auth::user();
    $minWithdrawAmount = Config::get('constants.MIN_WITHDRAW_AMOUNT'); 

    if( $value < $minWithdrawAmount ){
      $this->message = 'Withdraw amount must be greater then '. $minWithdrawAmount;
      return FAlSE;
    }else if($user->winning_balance < $value){
      $this->message = 'Insufficient fund to withdraw';
      return FAlSE;
    }

    return TRUE;
  }

  /**
   * Get the validation error message.
   *
   * @return string
   */
  public function message()
  {
      return $this->message;
  }
}
