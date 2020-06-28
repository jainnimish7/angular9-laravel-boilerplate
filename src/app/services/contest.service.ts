import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class ContestService {

  constructor(private http: HttpClient) { }

  // fetching contest Info
  getContestDetail(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'contest/get_contest_detail', data);
  }

  // fetching all the contest participants
  getContestParticipants(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'contest/contest_participants', data);
  }

  // get all the contest players
  getPlayers(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lineup/get_available_players', data);
  }

  // get all the contest players
  getPlayerDetail(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'player/player_card_details', data);
  }

  saveQueue(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lineup/prepare_add_to_player_queue', data);
  }

  getLeague() {
    return this.http.get(`${environment.API_URL}/` + 'common/get_all_league');
  }

  getGameStyle(season_id) {
    return this.http.get(`${environment.API_URL}/common/get_game_style/${season_id}`);
  }

  getSizes(league_id, game_style_id) {
    return this.http.get(`${environment.API_URL}/common/get-sizes/${league_id}/${game_style_id}`);
  }

  getTournaments(startDate, endDate, season_id) {
    return this.http.get(`${environment.API_URL}/contest/golf/get-tournaments?start_date=${startDate}&end_date=${endDate}&season_id=${season_id}`);
  }

  getPrizes(type) {
    return this.http.get(`${environment.API_URL}/contest/get-prizes?type=${type}`);
  }

  createContest(params: object) {
    return this.http.post(`${environment.API_URL}/contest/golf/create-championship`, params);
  }

  getContestTeamRoster(subContestId) {
    return this.http.get(`${environment.API_URL}/lineup/get-roaster-positions/${subContestId}`);
  }

}

