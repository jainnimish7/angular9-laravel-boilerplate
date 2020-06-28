<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

# Models
use App\Models\PaymentWithdrawTransaction;
use App\Models\PaymentHistoryTransaction;

# Helpers & Libraries
use Validator;
use Auth;
use Str;
use App\Rules\Finance\WithdrawAmount;
use App\Rules\Finance\WithdrawByValue;

class WithdrawController extends Controller
{

  public function withdraw_request(Request $request)
  {

    $this->user = Auth::user();

    $withdrawDetails = $request->post('withdraw_details');

    $validator = Validator::make($withdrawDetails, [
      'withdraw_amt' => ['required', new WithdrawAmount],
      'withdraw_by' => ['required'],
      'withdraw_by_value' => ['required', new WithdrawByValue($withdrawDetails['withdraw_by'])],
      'withdraw_type' => ['required'],
    ]);

    if ($validator->fails()) {

      return response()->json([
        'response_code' => 500,
        'service_name' => 'withdraw_request',
        'message' => 'Validation Failed',
        'global_error' => $validator->errors(),
      ]);
    }

    # Create Withdraw & History
    $this->create_withdraw($withdrawDetails);

    return response()->json([
      'response_code' => 200,
      'service_name' => 'withdraw_request',
      'message' => 'Withdraw Request has been sent. Your Request will be proccessed within 6-7 Working Days.',
    ]);
  }

  private function create_withdraw($withdrawDetails)
  {

    $newWithdraw = array(
      'transaction_unique_id' => Str::uuid(4), // used for further transaction
      'amount' => $withdrawDetails['withdraw_amt'],
      'status' => 0, // PENDING
      'created_date' => time_machine(),
      'modified_date' => time_machine(),
      'withdraw_type' => $withdrawDetails['withdraw_type'],
      'user_id' => $this->user->user_id
    );

    if ($withdrawDetails['withdraw_by'] == 'EMAIL') {
      $newWithdraw['email'] = $withdrawDetails['withdraw_by_value'];
    } else if ($withdrawDetails['withdraw_by'] == 'PHONE') {
      $newWithdraw['phone'] = $withdrawDetails['withdraw_by_value'];
    }

    $withdraw = PaymentWithdrawTransaction::create($newWithdraw);

    $history = array(
      'user_id' => $this->user->user_id,
      'currency_code' => 'USD',
      'payment_for' => 1,
      'created_date' => time_machine(),
      'payment_withdraw_transaction_id' => $withdraw->payment_withdraw_transaction_id,
      'is_processed' => 0,
      'payment_type' => 1,
      'amount' => $withdraw->amount,
      'description' => 'Withdraw amount using paypal'
    );

    PaymentHistoryTransaction::create($history);
  }
}
