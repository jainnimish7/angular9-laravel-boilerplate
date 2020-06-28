import { Component, OnInit, AfterViewInit, ElementRef, ViewChild } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { PaymentService } from '../../services/payment.service';
import { SharedService } from '../../services/shared.service';
import { environment } from '../../../environments/environment';
import { LoaderService } from '../../shared/loader/loader.service';

declare const paypal: any;
declare const $: any;
let that;
@Component({
  selector: 'app-deposit',
  templateUrl: './deposit.component.html',
  styleUrls: ['./deposit.component.scss']
})

export class DepositComponent implements OnInit, AfterViewInit {
  customAmount = 0;

  constructor(private toastr: ToastrService, private paymentService: PaymentService, private sharedService: SharedService, private loaderService: LoaderService) { }

  ngOnInit() {
    $('.amount-menu').on('click', 'li', function () {
      $('.amount-menu li.active').removeClass('active');
      $(this).addClass('active');
    });
  }

  ngAfterViewInit() {
    that = this;
    paypal.Buttons({
      style: {
        layout: 'horizontal',
        tagline: 'false',
        size: 'responsive',
        color: 'silver'
      },
      createOrder(data, actions) {
        that.loaderService.display(true);
        // This function sets up the details of the transaction, including the amount
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: that.customAmount
            }
          }]
        });
      },
      onApprove(data, actions) {
        // This function captures the funds from the transaction.
        return actions.order.capture().then((details) => {
          that.paymentService.processDepositPayment({ order_details: details })
            .subscribe((res: any) => {
              that.loaderService.display(false);
              that.sharedService.updateUser(res.data);
              that.toastr.success(res.message || 'Payment Completed Successfully!');
            }, (err: any) => {
              that.toastr.error(err.error.global_error || 'There was an error.');
            });
        });
      }, onCancel: function () {
        that.loaderService.display(false);
        that.toastr.error('Payment has been cancelled!');
      }
    }).render('#paypal-button-container');
  }

  selectAmount(amount) {
    this.customAmount = amount;
  }
}
