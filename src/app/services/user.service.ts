import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { forkJoin, throwError } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';
import { AuthenticationService } from './authentication.service';

@Injectable({ providedIn: 'root' })
export class UserService {

  constructor(private http: HttpClient, private authService: AuthenticationService) { }

  getUsers(params: object) {
    return this.http.post(`${environment.API_URL}/user/users`, params);
  }

  public getTransactionHistory(params: any, date: any) {
    return this.http.get(`${environment.API_URL}/payment-history?per_page=${params.per_page}&page=${params.current_page}&is_processed=${params.is_processed}&payment_type=${params.payment_type}&dates=${date}`);
  }

  // For verifying account
  public verifyAccount(token: string) {
    const url = `${environment.API_URL}` + '/activate_account/' + token;
    return this.http.get(url).pipe(map(response => {
      return response;
    }));
  }

  // Fetching user data for headers
  getUserData() {
    const url = `${environment.API_URL}/user_profile`;
    return this.http.post(url, {}).pipe(map(response => {
      return response;
    }));
  }

  // to reset the password
  public resetPassword(data: object) {
    return this.http.post(`${environment.API_URL}/reset/password`, data)
      .pipe(map(response => {
        return response;
      }));
  }

  // to sent an email to reset the password
  public forgotPassword(data: object) {
    return this.http.post(`${environment.API_URL}/forgot_password`, data)
      .pipe(map(response => {
        return response;
      }));
  }

  // to change the password
  public changePassword(data: object) {
    return this.http.post(`${environment.API_URL}/update_user_password`, data);
  }

  // to get the profile information and country list
  public getDataForProfile() {
    return forkJoin([
      this.http.post(`${environment.API_URL}/user_profile`, {}),
      this.http.get(`${environment.API_URL}/common/country_list`, {}),
    ]);
  }

  // to get the states for a country
  public getStatesByCountry(data: object) {
    return this.http.post(`${environment.API_URL}/my_profile/state_by_country`, data);
  }

  // to update profile
  public updateProfile(data: object) {
    return this.http.post(`${environment.API_URL}/update_user_profile`, data);
  }

  // to upload profile picture
  public uploadProfilePicture(data: object) {
    return this.http.post(`${environment.API_URL}/updated_user_profile_image`, data);
  }

  // to set timeout for user
  setTimeoutPeriod(data: object) {
    return this.http.post(`${environment.API_URL}/safer_gambling/self_timeout`, data);
  }

  // to set session reminder period for user
  setSessionReminderPeriod(data: object) {
    return this.http.post(`${environment.API_URL}/safer_gambling/set_reality_check`, data);
  }

  // to remove session reminder period for user
  removeSessionReminderPeriod() {
    return this.http.post(`${environment.API_URL}/safer_gambling/delete_reality_check`, {});
  }

  // to get session reminder period for user
  getSessionReminderPeriod() {
    return this.http.post(`${environment.API_URL}/safer_gambling/get_reality_check`, {});
  }

  // to set deposit limit for user
  setDepositLimit(data: object) {
    return this.http.post(`${environment.API_URL}/safer_gambling/set_deposit_limit`, data);
  }

  // to get all deposit limit of user
  getDepositLimit(data: object) {
    return forkJoin([
      this.http.post(`${environment.API_URL}/safer_gambling/get_deposit_limit`, data[0]),
      this.http.post(`${environment.API_URL}/safer_gambling/get_deposit_limit`, data[1]),
      this.http.post(`${environment.API_URL}/safer_gambling/get_deposit_limit`, data[2])
    ]);
  }

  // to delete deposit limit of user
  resetDepositLimit() {
    return this.http.post(`${environment.API_URL}/safer_gambling/delete_deposit_limit`, {});
  }

  // to set wager limit for user
  setWagerLimit(data: object) {
    return this.http.post(`${environment.API_URL}/safer_gambling/set_wager_limit`, data);
  }

  // to get all wager limit of user
  getWagerLimit(data: object) {
    return forkJoin([
      this.http.post(`${environment.API_URL}/safer_gambling/get_wager_limit`, data[0]),
      this.http.post(`${environment.API_URL}/safer_gambling/get_wager_limit`, data[1]),
      this.http.post(`${environment.API_URL}/safer_gambling/get_wager_limit`, data[2])
    ]);
  }

  // to delete wager limit of user
  resetWagerLimit() {
    return this.http.post(`${environment.API_URL}/safer_gambling/delete_wager_limit`, {});
  }
}
