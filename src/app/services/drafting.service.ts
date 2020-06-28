import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class DraftingService {

  constructor(private http: HttpClient) { }

  // fetching contest Info
  getContestDetails(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lineup/get_lineup_detail', data);
  }

  // fetching all the player rosters
  fetchPlayers(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'player/get_players_list', data);
  }

  // fetching one the player info
  fetchPlayerInfo(data) {
    return this.http.post(`${environment.API_URL}/` + 'player/player_card_details', data);
  }

  // get all the contest participants
  getUsersTeams(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'contest/get_all_team_roaster', data);
  }

  getExistingQueue(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lineup/get_draft_queue', data);
  }

  saveNewQueue(data: object) {
    return this.http.post(`${environment.API_URL}/` + 'lineup/save_queue_player', data);
  }

  getEmptyLineups(subContestId) {
    return this.http.get(`${environment.API_URL}/lineup/get_lineups/${subContestId}`);
  }

  getDraftHistory(subContestId) {
    return this.http.get(`${environment.API_URL}/lineup/draft-history/${subContestId}`);
  }

  getUsersLineup(lineupMasterId) {
    return this.http.get(`${environment.API_URL}/lineup/get-lineup-master/${lineupMasterId}`);
  }

  saveDraftPlayer(data: object) {
    return this.http.post(`${environment.API_URL}/lineup/draft_lineup`, data);
  }

  getRosterPositions(subContestId) {
    return this.http.get(`${environment.API_URL}/lineup/get-roaster-positions/${subContestId}`);
  }
}

