import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

import { AuthenticationService } from '../services/authentication.service';
import { LoaderService } from '../shared/loader/loader.service';

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
  constructor(
    private authService: AuthenticationService,
    private loaderService: LoaderService,
  ) { }

  private logoutInitiated = false;

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(map((res) => {
      if (!(('type' in res) && res.type === 0)) {
        this.logoutInitiated = false;
      }
      return res;
    }), catchError(err => {
      console.log('Error Log', err);
      if (err.status === 401) {
        if (!this.logoutInitiated) {
          this.logoutInitiated = true;
          this.loaderService.display(false);
          this.authService.logout('Your session has expired. Please login again!').subscribe();
        }
      } else {
        this.logoutInitiated = false;
      }
      return throwError(err);
    }));
  }
}
