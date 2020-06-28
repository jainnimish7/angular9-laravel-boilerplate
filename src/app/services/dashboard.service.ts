import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class DashboardService {

  constructor(private http: HttpClient) { }

  // fetching matches for next shutout contest only
  fetchTodaysMatches() {
    const url = `${environment.API_URL}/dashboard/fetch_shutout_matches`;
    return this.http.post(url, {}).pipe(map(response => {
      return response;
    }));
  }

  // fetching matches for next shutout contest only
  submitContactUsForm(data) {
    const url = `${environment.API_URL}/dashboard/contact_us_form`;
    return this.http.post(url, data).pipe(map(response => {
      return response;
    }));
  }

  // fetching upcoming live action contest
  fetchLiveActionContest() {
    const url = `${environment.API_URL}/dashboard/fetch_live_action_contests`;
    return this.http.post(url, {}).pipe(map(response => {
      return response;
    }));
  }

  // api call to see all the shutout prizes
  getShutoutPrizeList() {
    const url = `${environment.API_URL}` + '/' + 'dashboard/shutout_prize_list';
    return this.http.post(url, {}).pipe(map(response => {
      return response;
    }));
  }
}

