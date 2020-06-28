import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

import { environment } from '../../environments/environment';
import { ToastrService } from 'ngx-toastr';

@Injectable({ providedIn: 'root' })

export class AuthenticationService {
  private currentUserTokenSubject: BehaviorSubject<string>;
  public currentUserToken: Observable<string>;

  private isAuthenticatedSubject: BehaviorSubject<boolean>;
  public isAuthenticated: Observable<boolean>;

  constructor(private http: HttpClient, private router: Router, private toastr: ToastrService) {
    this.currentUserTokenSubject = new BehaviorSubject<string>(this.getToken());
    this.currentUserToken = this.currentUserTokenSubject.asObservable();

    const isLoggedIn = this.getToken().length > 0 ? true : false;
    this.isAuthenticatedSubject = new BehaviorSubject<boolean>(isLoggedIn);
    this.isAuthenticated = this.isAuthenticatedSubject.asObservable();
  }

  public login(data: object) {
    const params = { ...data, other_session_key: this.getToken() };
    return this.http.post(`${environment.API_URL}/login`, params).pipe(map((response: any) => {
      this.setAuthToken(response.token.access_token);
      return response;
    }), catchError((err: any) => {
      return throwError(err);
    }));
  }

  public logout(message?: string) {
    return this.http.post(`${environment.API_URL}/logout`, {}).pipe(map(response => {
      this.navigateToHome(message);
      return response;
    }), catchError((err: any) => {
      this.navigateToHome(message);
      return throwError(err);
    }));
  }

  navigateToHome(message) {
    this.removeAuthToken();
    this.router.navigate(['/']);
    if (message) {
      this.toastr.error(message);
    }
  }

  public get isUserAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }

  public get authenticatedToken(): string {
    return this.currentUserTokenSubject.value;
  }

  private setAuthToken(token: string) {
    const bearerToken = 'Bearer ' + token;
    localStorage.setItem('AuthToken', bearerToken);
    this.currentUserTokenSubject.next(bearerToken);
    this.isAuthenticatedSubject.next(true);
  }

  public getToken(): string {
    const token = localStorage.getItem('AuthToken') || '';
    if (token && this.isAuthenticatedSubject && !this.isUserAuthenticated) {
      this.isAuthenticatedSubject.next(true);
    }
    return token;
  }

  private removeAuthToken() {
    localStorage.clear();
    sessionStorage.clear();
    this.currentUserTokenSubject.next(null);
    this.isAuthenticatedSubject.next(false);
  }

  public register(data: object) {
    return this.http.post(`${environment.API_URL}/signup`, data).pipe(map(response => {
      return response;
    }));
  }

  public getCountries() {
    return this.http.get(`${environment.API_URL}/common/country_list`).pipe(map(response => {
      return response;
    }));
  }

  // to get the states for a country
  public getStatesByCountry(data: object) {
    return this.http.post(`${environment.API_URL}/common/state_by_country`, data);
  }

  public deactivateAccount() {
    return this.http.post(`${environment.API_URL}/auth/suspend`, {})
      .pipe(map(response => {
        return response;
      }), catchError(error => {
        console.error('Error in deactivating account: ', error);
        if (error.status === 401) {
          return this.logout('Your session has expired. Please login again!');
        }
        return throwError(error);
      }));
  }
}
