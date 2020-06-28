import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HomeComponent } from './home.component';
import { HomeRoutingModule } from './home-routing.module';
import { PostAuthenticationModule } from '../shared/layouts/post-authentication/post-authentication.module';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { DashboardComponent } from '../dashboard/dashboard.component';
import { PrivacyPolicyComponent } from '../privacy-policy/privacy-policy.component';
import { AboutUsComponent } from '../about-us/about-us.component';
import { TermsAndConditionsComponent } from '../terms-and-conditions/terms-and-conditions.component';
import { PipeModule } from '../pipes/pipes.module';
import { ReactiveFormsModule } from '@angular/forms';
import { LoginComponent } from '../login/login.component';
import { SignupComponent } from '../signup/signup.component';
import { ForgotPasswordComponent } from '../forgot-password/forgot-password.component';
import { FaqComponent } from '../faq/faq.component';
import { NgxCaptchaModule } from 'ngx-captcha';

@NgModule({
  declarations: [
    AboutUsComponent,
    DashboardComponent,
    FaqComponent,
    ForgotPasswordComponent,
    HomeComponent,
    LoginComponent,
    PrivacyPolicyComponent,
    SignupComponent,
    TermsAndConditionsComponent,
  ],
  imports: [
    BsDatepickerModule.forRoot(),
    CommonModule,
    HomeRoutingModule,
    PipeModule,
    PostAuthenticationModule,
    ReactiveFormsModule,
    NgxCaptchaModule,
  ]
})
export class HomeModule { }
