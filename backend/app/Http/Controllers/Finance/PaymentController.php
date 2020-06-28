<?php

namespace App\Http\Controllers\Finance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

# Models
use App\Models\PaymentDepositTransaction;
use App\Models\PaymentHistoryTransaction;
use App\Models\User;

# Helpers & Libraries
use Validator;
use Auth;
use Str;
use App\Helpers\Paypal;

class PaymentController extends Controller
{

  public function process_payment(Request $request){

    $this->user = Auth::user();

    $orderDetails = $request->post('order_details');

    $validator = Validator::make($orderDetails,[
      'id' => ['required']
    ]);

    if( $validator->fails() ){

      return response()->json([
        'response_code'=> 400,
        'service_name' => 'process_payment',
        'message'=> 'Validation Failed',
        'global_error'=> $validator->errors(),
      ], 400);

    }

    $order = Paypal::get_order($orderDetails['id']);

    if($order == FALSE || $order->result->status != 'COMPLETED' || $order->statusCode != 200){
      return response()->json([
        'response_code'=> 402,
        'service_name' => 'process_payment',
        'message'=> 'Process Payment',
        'global_error'=> 'Unable to capture order',
      ], 402);
    }

    //print_r($order);die;

    $saveOrder["user_id"] = $this->user->user_id;
    $saveOrder["transaction_id"] = $order->result->id;
    $saveOrder["transaction_date"] = $order->result->update_time;
    $saveOrder["transaction_status"] = $order->result->status;
    $saveOrder["payer_id"] = $order->result->payer->payer_id;
    $saveOrder["payer_email"] = $order->result->payer->email_address;
    $saveOrder["payer_phone"] = property_exists($order->result->payer, 'phone') ? $order->result->payer->phone->phone_number->national_number : 0;
    $saveOrder["first_name"] = $order->result->payer->name->given_name;
    $saveOrder["last_name"] = $order->result->payer->name->surname;
    $saveOrder["amount"] = intval($order->result->purchase_units[0]->amount->value);
    $saveOrder["currency_code"] = $order->result->purchase_units[0]->amount->currency_code;
    $saveOrder["created_at"] = time_machine();
    $saveOrder["updated_at"] = time_machine();

    // Update user balance
    $this->user->balance = $this->user->balance + $order->result->purchase_units[0]->amount->value;
    $this->user->save();

    // Create Deposit & History Transaction Entry
    $this->transaction_entry( $saveOrder );

    return response()->json([
      'response_code'=> 200,
      'service_name' => 'process_payment',
      'data' => $this->user,
      'message'=> 'Order captured and completed successfully',
    ]);

  }

  private function transaction_entry($transaction)
  {
    $newDeposit = array(
      'payment_transaction_unique_id' => Str::uuid(4),
      'payment_method' => 0,
      'user_id' => $this->user->user_id,
      'payment_request' => $transaction["amount"],
      'payer_id' =>  $transaction["payer_id"],
      'payer_email' =>  $transaction["payer_email"],
      'phone' =>  $transaction["payer_phone"],
      'first_name' =>  $transaction["first_name"],
      'last_name' =>  $transaction["last_name"],
      'transaction_id' => $transaction['transaction_id'],
      'order_time' =>  $transaction['transaction_date'],
      'payment_status' => $transaction['transaction_status'],
      'status' => 1,
      'added_to_user_balance' => $transaction["amount"],
      'date_created' => time_machine(),
      'date_modified' => time_machine(),
    );

    $deposit = PaymentDepositTransaction::create($newDeposit);

    $history = array(
      'user_id' => $this->user->user_id,
      'amount' => $transaction['amount'],
      'gateway_customer_id' => $transaction["payer_id"],
      'payment_for' => 0,
      'created_date' => time_machine(),
      'is_processed' => 1,
      'payment_type' => 0,
      'description' => 'Amount desposited into account',
      'payment_deposit_transaction_id' => $deposit->payment_transaction_id,
    );

    PaymentHistoryTransaction::create($history);
  }

}
