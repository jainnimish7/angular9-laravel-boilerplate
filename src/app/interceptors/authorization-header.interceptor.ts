import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable } from 'rxjs';

import { AuthenticationService } from '../services/authentication.service';

@Injectable()
export class AuthorizationHeaderInterceptor implements HttpInterceptor {
  constructor(
    private authService: AuthenticationService
  ) { }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const Authorization = this.authService.getToken();
    if (Authorization) {
      request = request.clone({ setHeaders: { Authorization } });
    }

    return next.handle(request);
  }
}
