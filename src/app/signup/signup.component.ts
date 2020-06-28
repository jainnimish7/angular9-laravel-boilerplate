import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from '../services/authentication.service';
import { ToastrService } from 'ngx-toastr';
import { encryptPassword } from '../services/utils.service';
import { CustomValidators } from '../shared/validators/custom-validator';
import { ReCaptcha2Component } from 'ngx-captcha';
import { SharedService } from '../services/shared.service';
import { environment } from '../../environments/environment';
import { LoaderService } from '../shared/loader/loader.service';

declare const $: any;

@Component({
  selector: 'app-signup',
  templateUrl: './signup.component.html',
  styleUrls: ['./signup.component.scss', '../shared/scss/shared.scss']
})
export class SignupComponent implements OnInit, AfterViewInit {
  private captchaToken: string;
  public readonly siteKey: string;
  enableRegister = false;
  signupForm: FormGroup;
  submitted = false;
  countryList = [];
  stateList = [];
  maxDate: Date;
  invalidUsername = '';
  formSubmitted = false;
  useGlobalDomain = false;
  showReferralInput = false;

  @ViewChild('captchaElem', { static: false }) captchaElem: ReCaptcha2Component;

  constructor(private formBuilder: FormBuilder, private authService: AuthenticationService,
    private toastr: ToastrService, private loaderService: LoaderService,
    private router: Router, private sharedService: SharedService) {
    this.siteKey = environment.captchaSiteKey;
  }

  ngOnInit() {
    if (this.authService.isUserAuthenticated) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/signup']);
    }
    this.maxDate = new Date();
    this.maxDate.setFullYear(this.maxDate.getFullYear() - 18);
    this.signupForm = this.formBuilder.group({
      firstName: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      lastName: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(20), CustomValidators.validUsername()]],
      email: ['', [Validators.required,
      Validators.pattern(new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i'))]],
      password: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      confirmPassword: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      dob: ['', [Validators.required]],
      master_state_id: [null, []],
      country: [null, [Validators.required]],
      agreeTerms: [false, Validators.required],
      //recaptcha: ['', Validators.required],
      referralCode: [null, []]
    }, {
      validator: CustomValidators.MatchPassword
    });

    this.authService.getCountries().pipe()
      .subscribe((response: any) => {
        this.countryList = response.data;
        this.signupForm.controls.country.setValue(this.countryList[0]);
        this.changeCountry();
      });
  }

  // loading jquery for popover after page initializes
  ngAfterViewInit() {
  }

  // Validate first name and last name to not allow number and special characters
  validateName(event: any) {
    const pattern = /[^a-zA-Z'\' ]/i;
    const inputChar = String.fromCharCode(event.charCode);
    if (pattern.test(inputChar)) {
      event.preventDefault();
    }
  }

  // getting form control values
  get f() {
    return this.signupForm.controls;
  }

  handleReset() {
    this.captchaToken = undefined;
  }

  // On change of country
  changeCountry() {
    let master_country_id = null;
    if (this.f.country.value) {
      master_country_id = this.f.country.value.master_country_id;
      this.signupForm.controls.master_state_id.setValue(null);
    }
    this.authService.getStatesByCountry({ master_country_id }).subscribe((response: any) => {
      this.stateList = response.data.state || [];
    }, () => {
      this.stateList = [];
      // this.toastr.error('There was an error in fetching the states. Please try again later!');
    });
  }

  handleSuccess(token: string) {
    this.enableRegister = true;
    this.captchaToken = token;
  }

  // trimming space from left or right if unnecessary space found
  trimSpace(fieldName: string) {
    this.f[fieldName].setValue(this.f[fieldName].value.trim());
  }

  validUsername(event: any) {
    if ((event.key >= 'a' && event.key <= 'z') || (event.key >= 'A' && event.key <= 'Z')
      || (event.key >= '0' && event.key <= '9') || (event.key >= '_')) {
      return true;
    }
    return false;
  }

  errorExist(field: string) {
    const error = this.f[field].errors;
    if (error && (error.required || (typeof (error.minlength) || typeof (error.maxlength)) !== 'undefined')) {
      return true;
    } else {
      return false;
    }
  }

  alreadyLoggedIn() {
    const token = localStorage.getItem('AuthToken') || '';
    if (token.length > 0) {
      this.router.navigate(['/my-profile']);
      this.toastr.success('You are already logged in.');
      return true;
    }
    return false;
  }

  // Native sign up form submission
  onSubmit() {

    if (!this.f.agreeTerms.value || this.alreadyLoggedIn()) {
      return;
    }
    this.submitted = true;
    if (this.signupForm.invalid) {
      return;
    }
    const data = {
      user_name: this.f.username.value,
      first_name: this.f.firstName.value,
      last_name: this.f.lastName.value,
      email: this.f.email.value.toLowerCase(),
      dob: this.validDateFormat(),
      password: encryptPassword(this.f.password.value),
      confirm_password: encryptPassword(this.f.confirmPassword.value),
      master_country_id: this.f.country.value.master_country_id,
      master_state_id: this.f.master_state_id.value,
      captcha: this.captchaToken,
      referral_code: this.f.referralCode.value
    };
    this.formSubmitted = true;
    this.loaderService.display(true);
    this.authService.register(data).pipe()
      .subscribe((response: any) => {
        this.resetSignupForm();
        this.loaderService.display(false);
        // this.captchaElem.resetCaptcha();
        this.formSubmitted = false;
        this.toastr.success(response.message);
      }, err => {
        this.loaderService.display(false);
        let errorMessage = '';
        if (err.error) {
          if (err.error.global_error.user_name) {
            errorMessage = err.error.global_error.user_name[0];
            this.invalidUsername = err.error.global_error.user_name[0];
          }
          if (err.error.global_error.email) {
            errorMessage = errorMessage.concat(' ', err.error.global_error.email[0]);
          }
          if (err.error.global_error.password) {
            errorMessage = errorMessage.concat(' ', err.error.global_error.password[0]);
          }
          if (err.error.global_error.confirm_password) {
            errorMessage = errorMessage.concat(' ', err.error.global_error.confirm_password[0]);
          }
          if (err.error.global_error.referral_code) {
            errorMessage = errorMessage.concat(' ', err.error.global_error.referral_code[0]);
          }
        }
        this.toastr.error(errorMessage || err.error.global_error || 'Some error occurred while signing up');
        this.formSubmitted = false;
      });
  }

  // Reset Signup form details
  resetSignupForm() {
    this.submitted = false;
    this.signupForm.reset();
    // this.captchaElem.resetCaptcha();
    this.enableRegister = false;
  }

  navigateUrl(user) {
    if (!(user.email && user.user_name)) {
      this.router.navigate(['/my-profile']);
    } else {
      this.router.navigate(['/']);
    }
  }

  validDateFormat() {
    const datePipe = new DatePipe('en-US');
    return datePipe.transform(this.f.dob.value, 'yyyy-MM-dd');
  }
}
