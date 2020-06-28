<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $connection = "pgsql2";
    protected $table = 'users.user';
    protected $primaryKey = 'user_id';
    protected $dates = ['dob'];
    protected $appends = ['full_name', 'main_balance'];
    public   $rules = [
        'first_name'    => 'required|max:50|min:2|alpha',
        'last_name'     => 'required|max:50|min:2|alpha',
        // 'email'         => 'required|email|regex:/^(.+)@(.+)$/',
        // 'phone_number'  => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|digits_between:5,15|unique:pgsql2.user,phone_number|numeric',
        // 'user_name'     => 'required|max:20|min:3|unique:pgsql2.user,user_name|regex:/^[a-zA-Z0-9_-]{3,30}$/',
        'dob'           => 'required',
        'master_country_id' => 'required|integer',
        'master_state_id'=>'required|integer',
    ];

    public   $rules_change_password = [
        'old_password'     => 'required|min:6|max:100',
        'new_password'     => 'required|min:6|max:100',
        'confirm_password' => 'required|same:new_password|min:6|max:100',
    ];

    protected $fillable = [
         'user_id',
         'user_unique_id',
         'first_name',
         'last_name',
         'user_name',
         'email',
         'password',
         'balance',
         'dob',
         'status',
         'status_reason',
         'new_password_key',
         'new_password_requested',
         'last_login',
         'last_ip',
         'created_date',
         'modified_date',
         'phone_number',
         'opt_in_email',
         'google_id',
         'facebook_id',
         'master_country_id',
         'master_state_id',
         'image',
         'address_1',
         'address_2',
         'city',
         'pincode',
         'provider_name',
         'referred_by',
         'is_referral_paid',
         'referral_balance'
        ];

    public $timestamps = false;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function AauthAcessToken()
    {
        return $this->hasMany('\App\Models\OauthAccessToken');
    }

    public function master_country()
    {
        return $this->belongsTo('App\Models\Country', 'master_country_id');
    }

    public function master_state()
    {
        return $this->belongsTo('App\Models\State','master_state_id','master_state_id');
    }

    public function setDobAttribute($date)
    {
      if (strlen($date)) {
              $this->attributes['dob'] = date('Y-m-d', strtotime($date));
      } else {
          $this->attributes['dob'] = null;
      }
    }

    public function getDobAttribute($date)
    {
      return $this->attributes['dob'] = date('Y-m-d', strtotime($date));
    }

    public function getFullNameAttribute()
    {
      return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function getMainBalanceAttribute(){
        return $this->balance + $this->winning_balance;
    }

}
