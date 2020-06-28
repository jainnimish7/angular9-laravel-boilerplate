import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { UserService } from '../services/user.service';
import { ToastrService } from 'ngx-toastr';
import { AuthenticationService } from '../services/authentication.service';
import { LoaderService } from '../shared/loader/loader.service';

declare const $: any;

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss', '../shared/scss/shared.scss']
})
export class ForgotPasswordComponent implements OnInit {
  forgotPasswordForm: FormGroup;
  submitted = false;

  constructor(private formBuilder: FormBuilder, private userService: UserService,
              private toastr: ToastrService, private authService: AuthenticationService,
              private router: Router, private loaderService: LoaderService) { }

  ngOnInit() {
    if (this.authService.isUserAuthenticated) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/forgot-password']);
    }
    this.forgotPasswordForm = this.formBuilder.group({
      email: ['', [Validators.required,
      Validators.pattern(new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i'))]]
    });
  }

  // getter for easy access to form fields
  get f() {
    return this.forgotPasswordForm.controls;
  }

  onSubmit() {
    this.submitted = true;
    if (this.forgotPasswordForm.invalid) {
      return;
    }
    const data = {
      email: this.f.email.value.toLowerCase()
    };
    this.loaderService.display(true);
    this.userService.forgotPassword(data).pipe()
      .subscribe((response: any) => {
        this.submitted = false;
        this.loaderService.display(false);
        this.toastr.success(response.message);
        this.router.navigate(['/login']);
        this.forgotPasswordForm.reset();
      }, err => {
        this.loaderService.display(false);
        if (err.status !== 401) {
          this.toastr.error(err.error.global_error || err.error.Message);
        }
      });
  }
}
