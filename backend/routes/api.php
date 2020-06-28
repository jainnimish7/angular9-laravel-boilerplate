<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test', 'PaymentController@process_payment');
Route::get('common/country_list', 'CommonController@country_list');
Route::post('common/state_by_country', 'CommonController@state_by_country');
Route::get('common/get_all_league','CommonController@get_all_league');
Route::get('common/get-game-styles','CommonController@get_game_styles');
//without authentication api

Route::get('common/country_list_code', 'CommonController@country_list_code');

Route::get('nonloginuser', function () {
  return Response::json(['message' => 'Unauthenticated User']);
});

Route::post('signup', 'AuthController@signup');
Route::get('activate_account/{link}', 'AuthController@activate_account');

Route::post('login', 'AuthController@user_login');

// forget password
Route::post('forgot_password', 'AuthController@forgot_password');
Route::post('reset/password', 'AuthController@reset_password');

  Route::middleware('auth:api')->group(function () {

    //common api for contest creation
    Route::group(['prefix' => 'common'], function()
    {
      Route::get('get_game_style/{season_id}', 'CommonController@get_game_style');
      Route::get('get-sizes/{league}/{gameStyle}', 'CommonController@get_sizes');
    });

    Route::post('logout', 'AuthController@logout');

    Route::post('user_profile', 'UserController@user_profile');

    Route::group(['prefix' => 'paypal'], function () {
      Route::post('process-payment', 'Finance\PaymentController@process_payment');
      Route::post('withdraw-request', 'Finance\WithdrawController@withdraw_request');
    });

    //payment history route
    Route::get('payment-history', 'Finance\PaymentHistoryController@get_payment_history');

    Route::post('update_user_profile', 'UserController@update_user_profile');
    Route::post('update_user_password', 'UserController@update_user_password');
    Route::post('updated_user_profile_image', 'UserController@updated_user_profile_image');

    // Contest Routes //API to join contest / Play now
    Route::group(['prefix' => 'lobby'], function(){
      Route::get('list', 'LobbyController@get_contest_list');
      Route::get('min_max_entry_fees', 'LobbyController@min_max_entry_fees');
      Route::post('join_contest', 'LobbyController@join_contest');
      Route::post('node_request', 'LobbyController@node_request');
    });

    //contest details
    Route::group(['prefix' => 'contest'], function(){
      Route::group(['prefix' => 'golf', 'namespace'=> "Leagues"], function(){
      //Golf Contest Routes
        Route::get('get-tournaments', 'GolfController@get_tournaments');
        Route::post('create-championship','GolfController@create_championship');
      });

      Route::get('pre_data', 'ContestController@pre_data');
      Route::get('get-prizes', 'ContestController@get_prizes');
      Route::get('get_match_weeks/{season_id}/{game_style_id}', 'ContestController@get_match_weeks');

       //end route create contest
      Route::post('contest_participants', 'ContestController@contest_participants');
      Route::post('get_contest_detail', 'ContestController@get_contest_detail');
      Route::post('get_all_team_roaster', 'ContestController@get_all_team_roaster');
      // Route::post('get_contest_detail', 'ContestController@get_contest_detail');
    });

    //player details
    Route::group(['prefix' => 'player'], function(){
      Route::get('get_players_list', 'PlayerController@get_players_list');
      Route::post('player_card_details','PlayerController@player_card_details');
    });

    //line up details
    Route::group(['prefix' => 'lineup'], function(){
      Route::post('get_lineup_detail', 'LineUpController@get_lineup_detail');
      Route::post('get_users_roster','LineUpController@get_users_roster');
      Route::post('get_available_players','LineUpController@get_available_players');
      Route::post('prepare_add_to_player_queue','LineUpController@prepare_add_to_player_queue');
      Route::post('get_draft_queue','LineUpController@get_player_drafted_queue');
      Route::post('save_queue_player','LineUpController@save_queue_player');
      Route::post('add_draft_queue','LineUpController@add_draft_queue');
      Route::post('get_lineup_data','LineUpController@get_lineup_data');

      Route::group(['namespace' => 'Draft'], function(){
        Route::put('auto-pick','AutoPickPlayerController@index');
        Route::get('draft-history/{sub_contest_uid}','DraftController@get_draft_history');
        Route::get('get-roaster-positions/{sub_contest_uid}','DraftController@get_roaster_positions');
        Route::get('get-lineup-master/{lineup_master_id}','DraftController@get_lineup_master');
      });
      Route::get('lineup_sub_contest/{sub_contest_id}','LineUpController@lineup_sub_contest');

      Route::get('get_lineups/{sub_contest_id}','LineUpController@get_lineups');

    });

});
