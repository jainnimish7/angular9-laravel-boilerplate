<?php

namespace App\Rules\Finance;

use Illuminate\Contracts\Validation\Rule;

# Helpers & Libraries
use Config;
use Auth;

class WithdrawByValue implements Rule
{
  /**
   * Create a new rule instance.
   *
   * @return void
   */
  public function __construct($withdrawBy)
  {
    $this->message = '';
    $this->withdrawBy = $withdrawBy;
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
    
    if ($this->withdrawBy == 'EMAIL' && !filter_var($value, FILTER_VALIDATE_EMAIL)){
      $this->message = "Withdraw by's value is not a valid email address";
      return FAlSE;
    }else if ($this->withdrawBy == 'PHONE' && !is_numeric($value)){
      $this->message = "Withdraw by's value must be a valid number";
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
