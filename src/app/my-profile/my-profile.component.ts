import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { ActivatedRoute } from '@angular/router';
import { encryptPassword } from '../services/utils.service';
import { CustomValidators } from '../shared/validators/custom-validator';
import { UserService } from '../services/user.service';
import { LoaderService } from '../shared/loader/loader.service';
import { SharedService } from '../services/shared.service';
import { AuthenticationService } from '../services/authentication.service';

declare const $: any;

@Component({
  selector: 'app-my-profile',
  templateUrl: './my-profile.component.html',
  styleUrls: ['./my-profile.component.scss'],
  providers: [UserService]
})

export class MyProfileComponent implements OnInit, AfterViewInit {
  changePasswordForm: FormGroup;
  updateProfileForm: FormGroup;
  changePasswordSubmitted = false;
  currentSection = 'account';
  updateProfileSubmitted = false;
  loading = true;
  countryList;
  stateList = [];
  selectedOption = 'showProfile';
  maxDate: Date;
  stateList$ = [];
  userProfile: any;

  private fileToUpload;
  private currentCountryIndex;

  constructor(private formBuilder: FormBuilder, private userService: UserService,
              private toastr: ToastrService, private loaderService: LoaderService,
              private sharedService: SharedService, private authService: AuthenticationService,
              private route: ActivatedRoute) { }

  ngOnInit() {
    const tab = this.route.snapshot.queryParams.tab;
    if (tab === '3') {
      this.currentSection = 'referral';
      $('.responsive-menu li.active').removeClass('active');
      $('#refer-friend').addClass('active');
    }
    this.maxDate = new Date();
    this.maxDate.setFullYear(this.maxDate.getFullYear() - 18);
    this.loaderService.display(true);
    this.userService.getDataForProfile().pipe()
      .subscribe((response: any) => {
        this.loading = false;
        if (response[0].data) {
          this.userProfile = response[0].data.user_profile;
          this.countryList = response[1].data;
          if (this.userProfile.master_country_id) {
            this.getStates(this.userProfile.master_country_id);
          }
          this.initialiseProfileForm();
        }
        this.loaderService.display(false);
      }, (err) => {
        if (err.status !== 401) {
          this.loaderService.display(false);
          this.toastr.error(err.error.global_error || err.error.Message || 'There was an error. Please try again later!');
        }
      });
    this.initialisePasswordForm();
  }

  // Initialise change password form
  initialisePasswordForm() {
    this.changePasswordForm = this.formBuilder.group({
      currentPassword: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      password: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
      confirmPassword: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(20)]],
    }, { validator: CustomValidators.MatchPassword });
  }

  initialiseProfileForm() {
    const dob = this.userProfile.dob ? new Date(this.userProfile.dob) : '';
    this.currentCountryIndex = this.countryList.findIndex((country) => country.master_country_id === this.userProfile.master_country_id);
    this.updateProfileForm = this.formBuilder.group({
      // user_name: [{ value: this.userProfile.user_name, disabled: (this.userProfile.user_name ? true : false) },
      // [Validators.required, Validators.minLength(3), Validators.maxLength(20), CustomValidators.validUsername()]],
      // email: [{
      //   value: this.userProfile.email,
      //   disabled: (this.userProfile.email ? true : false)
      // }, [Validators.required,
      // Validators.pattern(new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i'))]],
      first_name: [this.userProfile.first_name, [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      last_name: [this.userProfile.last_name, [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      // address: [this.userProfile.address, [Validators.required, Validators.minLength(5), Validators.maxLength(200)]],
      // city: [this.userProfile.city, [Validators.required, Validators.minLength(2), Validators.maxLength(100)]],
      // pincode: [this.userProfile.pincode, [Validators.required, Validators.minLength(4),
      // Validators.maxLength(20), Validators.pattern(new RegExp('^[a-z\\d\-\\s]+$', 'i'))]],
      master_state_id: [this.userProfile.master_state_id],
      master_country: [this.countryList[this.currentCountryIndex], [Validators.required]],
      dob: [dob, []],
      // opt_in_email: [this.userProfile.opt_in_email === '1' ? true : false],
      // phone_no: [this.userProfile.phone_no, [Validators.required, Validators.minLength(5), Validators.maxLength(15),
      // Validators.pattern(new RegExp('^[\\d\\-\\+\\(\\)]+$'))]],
    });
  }

  ngAfterViewInit() {
    $('.responsive-menu').on('click', 'li', function() {
      $('.responsive-menu li.active').removeClass('active');
      $(this).addClass('active');
    });
  }

  changeSection(section) {
    this.currentSection = section;
  }

  get changePasswordFields() {
    return this.changePasswordForm.controls;
  }

  get updateProfileFields() {
    return this.updateProfileForm.controls;
  }

  // Validate first name and last name to not allow number and special characters
  validateName(event: any) {
    const pattern = /[^a-zA-Z'\' ]/i;
    const inputChar = String.fromCharCode(event.charCode);
    if (pattern.test(inputChar)) {
      event.preventDefault();
    }
  }

  changePassword() {
    this.changePasswordSubmitted = true;
    if (this.changePasswordForm.invalid) {
      return;
    }
    const data = {
      old_password: encryptPassword(this.changePasswordFields.currentPassword.value),
      new_password: encryptPassword(this.changePasswordFields.password.value),
      confirm_password: encryptPassword(this.changePasswordFields.password.value),
    };
    this.loaderService.display(true);
    this.userService.changePassword(data).pipe()
      .subscribe((response: any) => {
        this.loaderService.display(false);
        this.toastr.success('Your password has been successfully changed.');
        this.changePasswordSubmitted = false;
        this.selectedOption = 'showProfile';
        this.changePasswordForm.reset();
      }, (err) => {
        if (err.status !== 401) {
          let errorMessage = '';
          this.loaderService.display(false);
          if (err.error.global_error) {
            if (err.error.global_error.old_password) {
              errorMessage = errorMessage.concat(' ', err.error.global_error.old_password);
            }
            if (err.error.global_error.new_password) {
              errorMessage = errorMessage.concat(' ', err.error.global_error.new_password);
            }
            if (err.error.global_error.confirm_password) {
              errorMessage = errorMessage.concat(' ', err.error.global_error.confirm_password);
            }
            this.toastr.error(errorMessage || 'Incorrect password, please try again or reset your password.');
          } else {
            this.toastr.error('There was an error while changing the passwords!');
          }
        }
      });
  }

  errorExist(field: string, formName: string) {
    const error = this[formName][field].errors;
    if (error && (error.required || (typeof (error.minlength) || typeof (error.maxlength)) !== 'undefined')) {
      return true;
    } else {
      return false;
    }
  }

  updateProfile() {
    this.updateProfileSubmitted = true;
    if (this.updateProfileForm.invalid) {
      return;
    }
    const data = {
      // user_name: this.updateProfileFields.user_name.value,
      // email: this.updateProfileFields.email.value.toLowerCase(),
      first_name: this.updateProfileFields.first_name.value,
      last_name: this.updateProfileFields.last_name.value,
      // address: this.updateProfileFields.address.value,
      // city: this.updateProfileFields.city.value,
      // pincode: this.updateProfileFields.pincode.value,
      master_state_id: this.updateProfileFields.master_state_id.value,
      master_country_id: this.updateProfileFields.master_country.value.master_country_id,
      dob: this.validDateFormat(),
      // opt_in_email: this.updateProfileFields.opt_in_email.value ? '1' : '0',
    };
    this.loaderService.display(true);
    this.userService.updateProfile(data).pipe()
      .subscribe((response: any) => {
        this.loaderService.display(false);
        this.toastr.success(response.message || 'Your profile has been successfully updated.');
        this.updateProfileSubmitted = false;
        this.selectedOption = 'showProfile';
        const newData = {
          full_name: data.first_name + ' ' + data.last_name,
          dob: data.dob,
          master_state: this.stateList.filter(s => s.master_state_id === this.updateProfileFields.master_state_id.value)[0],
          master_country: this.updateProfileFields.master_country.value,
        };
        this.userProfile = { ...this.userProfile, ...newData };
      }, (err) => {
        if (err.status !== 401) {
          this.loaderService.display(false);
          let errorMessage = '';
          if (err.error.global_error) {
            errorMessage = err.error.global_error;
          }
          this.toastr.error(errorMessage || 'There was an error in updating the profile information!');
        }
      });
  }

  // Cancel updating form & re-initialise with previous value
  cancelUpdate() {
    this.selectedOption = 'showProfile';
    this.initialiseProfileForm();
    this.initialisePasswordForm();
  }

  // on country change
  changeCountry() {
    const master_country_id = this.updateProfileFields.master_country.value.master_country_id;
    this.getStates(master_country_id);
  }

  // get list of states for country id
  private getStates(master_country_id) {
    this.authService.getStatesByCountry({ master_country_id }).subscribe((response: any) => {
      this.stateList = response.data.state || [];
      if (!this.updateProfileForm.controls.master_state_id) {
        this.updateProfileForm.controls.master_state_id.setValue(null);
      }
    }, () => {
      this.stateList = [];
    });
  }

  validDateFormat() {
    const datePipe = new DatePipe('en-US');
    return datePipe.transform(this.updateProfileFields.dob.value, 'dd-MM-yyyy');
  }

  fileChange(event: any) {
    this.fileToUpload = event.target.files[0];
    if (this.fileToUpload && !(this.fileToUpload.type === 'image/jpeg' || this.fileToUpload.type === 'image/png')) {
      this.toastr.error('Unsupported file format for the profile picture.');
      return;
    } else if (+(((this.fileToUpload.size / 1024) / 1024).toFixed(4)) > 4) {
      this.toastr.error('The file size should be less than 4 MB');
      return;
    }
    const profilePic = new Image();
    profilePic.onload = () => {
      if (profilePic.height < 200 || profilePic.width < 200) {
        this.toastr.error('The image dimensions should at least be 200 x 200.');
      } else {
        const formData = new FormData();
        formData.append('profileImage', this.fileToUpload, this.fileToUpload.name);
        this.loaderService.display(true);
        this.userService.uploadProfilePicture(formData).pipe()
          .subscribe((response: any) => {
            this.loaderService.display(false);
            this.userProfile.image = response.data.image;
            this.toastr.success(response.message || 'The profile picture has been successfully updated.');
          }, (err) => {
            if (err.status !== 401) {
              this.loaderService.display(false);
              this.toastr.error(err.error.global_error || 'There was an error in uploading the image!');
            }
          });
      }
      window.URL.revokeObjectURL(profilePic.src);
    };
    profilePic.src = window.URL.createObjectURL(event.target.files[0]);
  }

  trimSpace(fieldName: string) {
    this.updateProfileFields[fieldName].setValue(this.updateProfileFields[fieldName].value.trim());
  }

  // Checking that user can't update before 24 hours
  isVerifyButtonDisabled(lastModifiedDate) {
    if (lastModifiedDate) {
      return this.sharedService.isLessThan24Hours(lastModifiedDate);
    } else {
      return false;
    }
  }
}
