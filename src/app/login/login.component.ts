import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from '../services/authentication.service';
import { encryptPassword } from '../services/utils.service';
import { ToastrService } from 'ngx-toastr';
import { LoaderService } from '../shared/loader/loader.service';

declare const $: any;

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss', '../shared/scss/shared.scss']
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  submitted = false;

  constructor(private formBuilder: FormBuilder, private authService: AuthenticationService,
    private router: Router, private loaderService: LoaderService,
    private toastr: ToastrService) {
  }

  ngOnInit() {
    if (this.authService.isUserAuthenticated) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/login']);
    }
    this.loginForm = this.formBuilder.group({
      email: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      remember_me: [false],
    });
  }

  // getter for easy access to form fields
  get f() {
    return this.loginForm.controls;
  }

  alreadyLoggedIn() {
    const token = localStorage.getItem('AuthToken') || '';
    if (token.length > 0) {
      $('#login-modal').modal('hide');
      this.router.navigate(['/my-profile']);
      this.toastr.success('You are already logged in.');
      return true;
    }
    return false;
  }

  onSubmit() {
    if (this.alreadyLoggedIn()) {
      return;
    }
    this.submitted = true;
    // stop here if form is invalid
    if (this.loginForm.invalid) {
      return;
    }
    const data = {
      email: this.f.email.value.toLowerCase(),
      password: encryptPassword(this.f.password.value),
      device_id: 1,
      device_type: 1,
      // remember_me: this.f.remember_me.value,
    };
    this.loaderService.display(true);
    this.authService.login(data).pipe()
      .subscribe((response: any) => {
        this.resetForm();
        this.loaderService.display(false);
        this.navigateUrl(response.data);
      }, (err: any) => {
        this.loaderService.display(false);
        let errorMessage = '';
        if (err.error && err.error.error) {
          if (err.error.error.email) {
            errorMessage = err.error.error.email;
          }
          if (err.error.error.password) {
            errorMessage = errorMessage.concat(' ', err.error.error.password);
          }
        }
        console.log(errorMessage);
        this.toastr.error(errorMessage || err.error.global_error || 'Some error occurred while logging in');
      });
  }

  // Open popup for google login
  // signInWithGoogle() {
  //   if (this.alreadyLoggedIn()) {
  //     return;
  //   }
  //   this.sharedService.socialLogin('google')
  //     .then(data => {
  //       this.authService.login(data).pipe()
  //         .subscribe((response: any) => {
  //           this.navigateUrl(response.data);
  //         }, err => {
  //           this.toastr.error(err.error.global_error || 'Some error occurred while log in');
  //         });
  //     });
  // }

  // Sign in using facebook
  // signInWithFB(): void {
  //   if (this.alreadyLoggedIn()) {
  //     return;
  //   }
  //   this.sharedService.socialLogin('facebook')
  //     .then(data => {
  //       this.dismissModal();
  //       this.authService.login(data).pipe()
  //         .subscribe((response: any) => {
  //           this.navigateUrl(response.data);
  //         }, err => {
  //           this.toastr.error(err.error.global_error || 'Some error occurred while log in');
  //         });
  //     });
  // }

  // reset the login form details and show success login notification.
  resetForm() {
    this.submitted = false;
    this.toastr.success('Login Successfully');
    this.loginForm.reset();
  }

  // Navigate to my account if email or username is not filled
  private navigateUrl(user: any) {
    if ((user.email && user.user_name)) {
      this.router.navigate(['/my-profile']);
    } else {
      this.router.navigate(['/']);
    }
  }
}
