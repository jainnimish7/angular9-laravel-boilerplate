import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MyAccountRoutingModule } from './my-account-routing.module';
import { PostAuthenticationModule } from '../shared/layouts/post-authentication/post-authentication.module';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { PipeModule } from '../pipes/pipes.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgxCaptchaModule } from 'ngx-captcha';

import { MyProfileComponent } from './my-profile.component';
import { DepositComponent } from './deposit/deposit.component';
import { WithdrawalComponent } from './withdrawal/withdrawal.component';
import { TransactionHistoryComponent } from './transaction-history/transaction-history.component';
import { ReferralComponent } from './referral/referral.component';

@NgModule({
  declarations: [
    DepositComponent,
    MyProfileComponent,
    TransactionHistoryComponent,
    WithdrawalComponent,
    ReferralComponent,
  ],
  imports: [
    BsDatepickerModule.forRoot(),
    CommonModule,
    MyAccountRoutingModule,
    PipeModule,
    FormsModule,
    PostAuthenticationModule,
    ReactiveFormsModule,
    FormsModule,
    NgxCaptchaModule,
  ]
})
export class MyAccountModule { }
