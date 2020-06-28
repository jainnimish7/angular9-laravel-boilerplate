import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
// import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { NgxCaptchaModule } from 'ngx-captcha';

import { ToastrModule } from 'ngx-toastr';
import { AppRoutingModule } from './app-routing.module';
import { PostAuthenticationModule } from './shared/layouts/post-authentication/post-authentication.module';
import { PipeModule } from './pipes/pipes.module';

// Components
import { AppComponent } from './app.component';
import { ConfirmAccountComponent } from './confirm-account/confirm-account.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { NotFoundComponent } from './not-found/not-found.component';
import { CanDeactivateGuard } from './can-deactivate/can-deactivate.guard';
// Services
import { SharedService } from './services/shared.service';
import { AuthorizationHeaderInterceptor, ErrorInterceptor } from './interceptors';

@NgModule({
  declarations: [
    AppComponent,
    ConfirmAccountComponent,
    ResetPasswordComponent,
    NotFoundComponent,
  ],
  imports: [
    AppRoutingModule,
    BrowserModule,
    BrowserAnimationsModule,
    // BsDatepickerModule.forRoot(),
    FormsModule,
    HttpClientModule,
    PipeModule,
    PostAuthenticationModule,
    ReactiveFormsModule,
    NgxCaptchaModule,
    ToastrModule.forRoot({
      closeButton: true,
      positionClass: 'toast-top-right',
      maxOpened: 1,
      autoDismiss: true,
    }),
  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: AuthorizationHeaderInterceptor, multi: true },
    { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true },
    CanDeactivateGuard,
    SharedService],
  bootstrap: [AppComponent]
})
export class AppModule { }
