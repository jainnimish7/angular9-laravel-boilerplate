<?php

namespace App\Helpers;

# Helpers & Libraries
use Config;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

class Paypal
{
  private static function setup_environment(){
    $clientId = Config::get('constants.PAYPAL_CLIENT_ID');
    $clientSecret = Config::get('constants.PAYPAL_CLIENT_SECRET');
    $paypalMode = Config::get('constants.PAYPAL_MODE');
    
    if( $paypalMode == 'sandbox' ){
      return new SandboxEnvironment($clientId, $clientSecret);
    }elseif( $paypalMode == 'production' ){
      return new ProductionEnvironment($clientId, $clientSecret);
    }
  }

  private static function setup_client(){
    return new PayPalHttpClient(Self::setup_environment());
  }
  
  public static function get_order($order_id){
  
    $client = Self::setup_client();
  
    try {
      $order = $client->execute(new OrdersGetRequest($order_id));
      return $order;
    } catch (\Exception $ex) {
      // log_message('error', 
      // "Status Code: $ex->statusCode\n".
      // "Body: ".$ex->getMessage());
      return FALSE;
    }
  }

}