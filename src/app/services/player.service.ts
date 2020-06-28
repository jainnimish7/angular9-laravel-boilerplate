import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class PlayerService {

  constructor(private http: HttpClient) { }

  // fetching player recap details
  fetchPlayerRecap(player: any) {
    const url = `${environment.API_URL}/player/fetch_player_recap`;
    return this.http.post(url, player).pipe(map(response => {
      return response;
    }));
  }

  // fetching player stats details
  fetchPlayerStats(player: any) {
    const url = `${environment.API_URL}/player/fetch_player_statistics`;
    return this.http.post(url, player).pipe(map(response => {
      return response;
    }));
  }

  fetchAvgStats(player: any) {
    const url = `${environment.API_URL}/player/fetch_average_statistics`;
    return this.http.post(url, player).pipe(map(response => {
      return response;
    }));
  }

  fetchLiveStats(player: any) {
    const url = `${environment.API_URL}/player/current_stats`;
    return this.http.post(url, player).pipe(map(response => {
      return response;
    }));
  }
}
