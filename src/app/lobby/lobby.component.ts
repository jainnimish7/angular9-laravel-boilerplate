import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { Router } from '@angular/router';
import { LobbyService } from '../services/lobby.service';
import { LoaderService } from '../shared/loader/loader.service';
import { UserService } from '../services/user.service';
import { formatDate, dateFormatString } from '../services/utils.service';
import { SharedService } from '../services/shared.service';

const INITIAL_PARAMS = {
  maxFee: 0,
  minFee: 0,
  selectedLeague: '2',
  selectedGameStyle: 'championship',
  selectedDraft: '',
  keyword: ''
};
declare const $: any;
@Component({
  selector: 'app-lobby',
  templateUrl: './lobby.component.html',
  styleUrls: ['./lobby.component.scss'],
})

export class LobbyComponent implements OnInit {
  contestList = [];
  formatDate = formatDate;
  dateFormatString = dateFormatString;
  maxFee = 0;
  minFee = 0;
  leagues: any;
  gameStyles: any;
  selectedLeague = 1;
  selectedGameStyle = 1;
  selectedDraft = 1;
  url = '';
  remainingBalance = 0;
  selectedContestDetail: any;
  selection = 'before';
  teamName: any;
  value = 0;
  enabled = false;

  public params: any = { ...INITIAL_PARAMS };
  constructor(private router: Router, private toastr: ToastrService, private lobbyService: LobbyService,
    private loaderService: LoaderService, private userService: UserService, private sharedService: SharedService) { }

  ngOnInit() {
    this.loaderService.display(true);
    this.getSideFilters();
  }

  getContests() {
    this.url = `min_entry_fee=${this.params.minFee}&max_entry_fee=${this.params.maxFee}&keyword=${this.params.keyword}&league_id=${this.params.selectedLeague}&game_style=${this.params.selectedGameStyle}&draft_speed=${this.params.selectedDraft}`;
    this.lobbyService.getContestList(this.url).pipe()
      .subscribe((contests: any) => {
        this.contestList = contests.data;
        this.loaderService.display(false);
      }, (err) => {
        if (err.status !== 401) {
          this.contestList = [];
          this.loaderService.display(false);
          this.toastr.error(err.error.global_error || err.error.Message || 'There was an error. Please try again later!');
        }
      });
  }

  getSideFilters() {
    this.lobbyService.getFilters().pipe()
      .subscribe((response: any) => {
        if (response[0].data) {
          this.params.maxFee = response[0].data[0].max_fee;
          this.params.minFee = response[0].data[0].min_fee;
          this.minFee = this.params.minFee;
          this.maxFee = this.params.maxFee;
        }
        if (response[1].data) {
          this.leagues = response[1].data;
        }
        if (response[2].data) {
          this.gameStyles = response[2].data;
        }
        this.getContests();
      }, (err) => {
        if (err.status !== 401) {
          this.loaderService.display(false);
          this.toastr.error(err.error.global_error || err.error.Message || 'There was an error. Please try again later!');
        }
      });
  }

  public searchByLeague(leagueId: any) {
    this.params.selectedLeague = leagueId || '';
  }

  public searchByGameStyle(abbr: any) {
    this.params.selectedGameStyle = abbr || '';
  }

  public searchFilter(type: any) {
    if (type === 'reset') {
      this.params = { ...INITIAL_PARAMS };
      this.params.minFee = this.minFee;
      this.params.maxFee = this.maxFee;
      // FIND A GOOD WAY TO REST SLIDER
      this.selection = this.selection === 'before' ? 'after' : 'before';
    }
    this.getContests();
  }

  changeEntryFee(event) {
    this.params.minFee = event[0];
    this.params.maxFee = event[1];
  }

  joinContest() {
    this.loaderService.display(true);
    this.lobbyService.joinContest({
      entry_fee: this.selectedContestDetail.entry_fees,
      contest_size: this.selectedContestDetail.size,
      league_id: this.selectedContestDetail.season.league.league_id,
      contest_id: this.selectedContestDetail.contest_id,
      team_name: this.teamName,
    })
      .subscribe((res: any) => {
        this.userService.getUserData().subscribe((usr: any) => {
          this.sharedService.updateUser(usr.data.user_profile);
        });
        this.loaderService.display(false);
        this.toastr.success('Successfully joined the contest');
        this.router.navigateByUrl('/contest-setting/' + this.selectedContestDetail.contest_uid + '/' + res.message.sub_contest_id);
      }, (err) => {
        this.loaderService.display(false);
        this.toastr.error(err.error.global_error || 'Error while joining the contest');
      });
  }

  // Open Confirmation modal
  openConfirmationModal(contest) {
    this.userService.getUserData().subscribe((usr: any) => {
      const { main_balance } = usr.data.user_profile;
      this.remainingBalance = +main_balance - contest.entry_fees;
      this.selectedContestDetail = contest;
      $('#confirmPurchaseModal').appendTo('body').modal('show');
    });
  }
}
