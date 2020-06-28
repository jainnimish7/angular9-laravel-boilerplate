<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

# Models
use App\Models\PaymentHistoryTransaction;

class PaymentHistoryController extends Controller
{
   public function get_payment_history(Request $request)
   {
   		$this->user = Auth::user();
         // $current_date = time_machine();
         // $todate =date('Y-m-d H:i:s', strtotime($current_date));
   		$historyTransaction = new PaymentHistoryTransaction;
   		$historyTransaction = $historyTransaction->with('deposite_history','withdraw_histroy');


   		 // Date Range Filter
      $dates = json_decode($request->dates);
      if( isset($dates->fromdate) && isset($dates->todate) ){
        $historyTransaction = $historyTransaction->whereBetween('created_date', [$dates->fromdate , $dates->todate]);
      }

      if(isset($request->is_processed) && $request->is_processed != ""  && $request->is_processed > -1)
      {
        $historyTransaction = $historyTransaction->where('is_processed', $request->is_processed);
      }

      if(isset($request->payment_type) && $request->payment_type > -1)
      {
        $historyTransaction = $historyTransaction->where('payment_type', $request->payment_type);
      }

      $historyTransaction = $historyTransaction->where('user_id',$this->user->user_id);
   		$historyTransaction = $historyTransaction->orderBy('created_date','DESC');
   		$historyTransaction = $historyTransaction->paginate($request->per_page);

   	if($historyTransaction->count() == 0){
         return response()->json([
           'service_name' => 'transaction_history',
           'global_error'=> 'No history transaction found',
         ], 404);
      }
       return response()->json([
         'service_name' => 'transaction_history',
         'data' => $historyTransaction,
         'message'=> 'History transaction found',
       ], 200);
   }
}
