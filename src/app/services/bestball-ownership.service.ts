import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { AuthenticationService } from './authentication.service';
import { map, catchError } from 'rxjs/operators';
import { forkJoin } from 'rxjs';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class BestballOwnershipService {

  constructor(private http: HttpClient, private authService: AuthenticationService) { }

  // fetching all fees and size of league
  getStatus(id) {
    return this.http.get(`${environment.API_URL}/common/get-status?id=${id}`);
  }
}

