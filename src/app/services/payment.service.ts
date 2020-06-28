import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class PaymentService {

  constructor(private http: HttpClient) { }

  // to process payment deposited
  processDepositPayment(data: object) {
    return this.http.post(`${environment.API_URL}/paypal/process-payment`, data)
      .pipe(map(response => {
        return response;
      }));
  }

  // to withdrawal payment process
  processWithdrawalPayment(data: object) {
    return this.http.post(`${environment.API_URL}/paypal/withdraw-request`, { withdraw_details: data })
      .pipe(map(response => {
        return response;
      }));
  }
}
