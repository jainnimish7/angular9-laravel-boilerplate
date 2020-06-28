import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, Params } from '@angular/router';
import { encryptPassword } from '../services/utils.service';
import { UserService } from '../services/user.service';
import { ToastrService } from 'ngx-toastr';

import { CustomValidators } from '../shared/validators/custom-validator';
import { AuthenticationService } from '../services/authentication.service';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss', '../shared/scss/shared.scss']
})
export class ResetPasswordComponent implements OnInit {
  resetPasswordForm: FormGroup;
  submitted = false;
  token: string;

  constructor(private formBuilder: FormBuilder, private userService: UserService,
              private route: ActivatedRoute, private toastr: ToastrService,
              private router: Router, private authService: AuthenticationService) { }

  ngOnInit() {
    if (this.authService.isUserAuthenticated) {
      this.router.navigate(['/my-profile']);
      this.toastr.success('You are already logged in.');
    } else {
      this.route.params.forEach((params: Params) => {
        this.token = params.token;
      });
    }

    this.resetPasswordForm = this.formBuilder.group({
      password: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      confirmPassword: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]]
    }, {
      validator: CustomValidators.MatchPassword
    });
  }

  get f() {
    return this.resetPasswordForm.controls;
  }

  onSubmit() {
    this.submitted = true;
    if (this.resetPasswordForm.invalid) {
      return;
    }
    const data = {
      unique_token: this.token,
      password: encryptPassword(this.f.password.value),
      confirm_password: encryptPassword(this.f.confirmPassword.value)
    };

    this.userService.resetPassword(data).pipe()
      .subscribe(() => {
        this.router.navigate(['/login']);
        this.toastr.success('Password Reset successfully');
      }, err => {
        let errorMessage = 'Error while resetting password';
        if (err.error && err.error.global_error) {
          errorMessage = err.error.global_error || err.error.message;
        }
        this.toastr.error(errorMessage);
      }
      );
  }
}
