import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { formatDate, setDateformat } from '../../services/utils.service';
import { UserService } from '../../services/user.service';
import { LoaderService } from '../../shared/loader/loader.service';
import { SharedService } from '../../services/shared.service';

const INITIAL_PARAMS = {
  per_page: 5,
  current_page: 1,
  is_processed: '-1',
  payment_type: '-1',
  dates: '',
};
@Component({
  selector: 'app-transaction-history',
  templateUrl: './transaction-history.component.html',
  styleUrls: ['./transaction-history.component.scss']
})

export class TransactionHistoryComponent implements OnInit {
  transactionHistoryList = [];
  formatDateTime = formatDate;
  params: any = { ...INITIAL_PARAMS };
  totalTxn = 0;
  totalPages = 0;
  public maxDate = new Date();
  public url = '';
  public paymentType = {
    0: 'CREDIT',
    1: 'DEBIT'
  };
  public STATUS = {
    0: 'Pending',
    1: 'Completed',
    2: 'Cancelled',
  };
  constructor(private toastr: ToastrService, private userService: UserService,
              private loaderService: LoaderService, public sharedService: SharedService) { }

  ngOnInit() {
    this.getTransactionHistory();
  }

  getTransactionHistory() {
    const date = this.params.dates.length == 2 ? {
      fromdate: `${setDateformat(this.params.dates[0])} 00:00:00`,
      todate: `${setDateformat(this.params.dates[1])} 23:59:59`,
      time_zone: this.params.dates[0].toString().split(' ')[5],
    } : [];
    this.loaderService.display(true);
    this.userService.getTransactionHistory(this.params, JSON.stringify(date))
      .subscribe((res: any) => {
        this.loaderService.display(false);
        this.transactionHistoryList = res.data.data;
        this.createPaginationItem(res.data.total);
      }, (err: any) => {
        this.transactionHistoryList = [];
        this.toastr.error(err.error.global_error || 'There was an error. Please try again later!');
        this.loaderService.display(false);
      });
  }

  private createPaginationItem(totalTxn: number) {
    this.totalTxn = totalTxn;
    const maxPages: number = Math.ceil(totalTxn / this.params.per_page);
    this.totalPages = maxPages;
  }

  public paginateList(newPage: number) {
    if (this.params.current_page === newPage) { return false; }
    this.params.current_page = newPage;
    this.getTransactionHistory();
  }

  public nextOrPreviousPage(deviation: number) {
    this.params.current_page = this.params.current_page + deviation;
    this.getTransactionHistory();
  }

  filterByDate(event) {
    if (event) {
      this.params.dates = event;
      this.getTransactionHistory();
    }
  }

  public searchFilter(type: any) {
    if (type === 'reset') {
      this.params = { ...INITIAL_PARAMS };
    }
    this.params.current_page = 1;
    this.getTransactionHistory();
  }
}
