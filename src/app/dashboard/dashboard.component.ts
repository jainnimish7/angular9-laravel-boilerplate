import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';

import { AuthenticationService } from '../services/authentication.service';
import { DashboardService } from '../services/dashboard.service';
import { formatDate } from '../services/utils.service';
import { LoaderService } from '../shared/loader/loader.service';
import { UserService } from '../services/user.service';
import { SharedService } from '../services/shared.service';
import { PLAYER_LIST } from './players-list';

declare const $: any;

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})

export class DashboardComponent implements OnInit, OnDestroy {
  contestModalData: any;
  entry_fee: any;
  formatDateTime = formatDate;
  selectedContest: any;
  shutoutJackpotAmount: number;
  liveActionFee: any;
  liveActionContests = [];
  liveActionJackpotAmount: any;
  matchDate: any;
  shutoutContestId: number;
  shutoutContestName: string;
  shutoutMatches = [];
  prizes = [];
  demoPlayers = PLAYER_LIST;
  isMatchModalOpened = false;
  gameType: any;
  pendingJackpot = false;

  constructor(public authService: AuthenticationService, public loaderService: LoaderService,
              public sharedService: SharedService) {
  }

  ngOnInit() {
    $('.responsive-menu li.active').removeClass('active');
    $('#home-button').addClass('active');
  }

  // Assign contest unique id
  openLeagueInfoModal(contest) {
    this.contestModalData = contest;
    $('#leagueModal').appendTo('body').modal('show');
  }

  openShutoutInfoModal() {
    this.isMatchModalOpened = true;
    $('#shutoutInfoModal').appendTo('body').modal('show');
  }

  getEntryFee(contest) {
    if (contest.entry_fee > 0) {
      this.liveActionFee = `$${contest.entry_fee}`;
    } else if (contest.charity_amount > 0) {
      this.liveActionFee = `$${contest.charity_amount} <i class="fa fa-handshake-o"></i>`;
    } else {
      this.liveActionFee = 'Free';
    }
    return this.liveActionFee;
  }

  ngOnDestroy() {
    const modal = document.getElementById('leagueModal');
    if (modal) {
      modal.remove();
    }
  }
}
