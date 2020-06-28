import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { AuthenticationService } from './authentication.service';
import { map, catchError } from 'rxjs/operators';
import { forkJoin } from 'rxjs';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class LobbyService {

  constructor(private http: HttpClient, private authService: AuthenticationService) { }

  // fetching all fees and size of league
  fetchAllFeeAndSize(data: Object) {
    return this.http.post(`${environment.API_URL}/` + 'lobby/fetch_master_data', data);
  }

  // fetching size corrosponding to entry fees
  fetchLeagueSize(data: Object) {
    return this.http.post(`${environment.API_URL}/` + 'lobby/fetch_size', data)
      .pipe(map(response => {
        return response;
      }));
  }

  getLiveActionMatches(data: Object) {
    return this.http.post(`${environment.API_URL}/` + 'lobby/fetch_live_action_contests', data)
      .pipe(map(response => {
        return response;
      }));
  }
  getLobbyFiltersData() {
    return this.http.post(`${environment.API_URL}/` + 'lobby/fetch_lobby_filters_data', {})
      .pipe(map(response => {
        return response;
      }));
  }

  getContestList(url) {
    return this.http.get(`${environment.API_URL}/lobby/list?${url}`)
      .pipe(map(response => {
        return response;
      }));
  }

  joinContest(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lobby/join_contest', data)
      .pipe(map(response => {
        return response;
      }));
  }

  getFilters() {
    return forkJoin([
      this.http.get(`${environment.API_URL}/lobby/min_max_entry_fees`),
      this.http.get(`${environment.API_URL}/common/get_all_league`),
      this.http.get(`${environment.API_URL}/common/get-game-styles`),
    ]);
  }
}

