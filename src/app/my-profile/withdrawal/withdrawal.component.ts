import { Component, OnInit, Input } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { PaymentService } from 'src/app/services/payment.service';
import { LoaderService } from 'src/app/shared/loader/loader.service';

@Component({
  selector: 'app-withdrawal',
  templateUrl: './withdrawal.component.html',
  styleUrls: ['./withdrawal.component.scss']
})

export class WithdrawalComponent implements OnInit {
  @Input() userProfile: any;
  withdrawOptions = ['EMAIL', 'PHONE'];

  submitted = false;
  withdrawalForm: FormGroup;

  constructor(private toastr: ToastrService, private formBuilder: FormBuilder,
    private paymentService: PaymentService, private loaderService: LoaderService) { }

  ngOnInit() {
    this.withdrawalForm = this.formBuilder.group({
      amount: ['', [Validators.required, Validators.pattern('^[0-9]*$'),
      Validators.min(10), Validators.max(+this.userProfile.winning_balance)]],
      withdrawalBy: [null, Validators.required],
      email: [],
      phone: []
    });
    this.setEmailValidators();
  }

  setEmailValidators() {
    const email = this.f.email;
    const phone = this.f.phone;

    this.f.withdrawalBy.valueChanges
      .subscribe(type => {
        if (type === 'EMAIL') {
          this.f.email.setValidators([Validators.required, Validators.pattern(new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i'))]);
          phone.setValidators(null);
        } else {
          email.setValidators(null);
          phone.setValidators([Validators.required, Validators.pattern(/^-?(0|[1-9]\d*)?$/)]);
        }
        email.updateValueAndValidity();
        phone.updateValueAndValidity();
      });
  }

  // getter for easy access to form fields
  get f() {
    return this.withdrawalForm.controls;
  }

  // trimming space from left or right if unnecessary space found
  trimSpace(fieldName: string) {
    this.f[fieldName].setValue(this.f[fieldName].value.trim());
  }

  onSubmit() {
    this.submitted = true;
    // stop here if form is invalid
    if (this.withdrawalForm.invalid) {
      return;
    }
    const data = {
      withdraw_amt: this.f.amount.value.toLowerCase(),
      withdraw_by: this.f.withdrawalBy.value,
      withdraw_by_value: this.f.withdrawalBy.value === 'EMAIL' ? this.f.email.value : this.f.phone.value,
      withdraw_type: 1
    };
    this.loaderService.display(true);
    this.paymentService.processWithdrawalPayment(data)
      .subscribe((response: any) => {
        this.loaderService.display(false);
        if (response.response_code === 200) {
          this.toastr.success(response.message);
        } else {
          this.handleWithdrawError(response);
        }
      }, err => {
        this.handleWithdrawError(err);
      });
  }

  handleWithdrawError(err) {
    this.loaderService.display(false);
    let errorMessage = '';
    if (err.global_error) {
      if (err.global_error.withdraw_amt) {
        errorMessage = err.global_error.withdraw_amt[0];
      }
      if (err.global_error.withdraw_by_value) {
        errorMessage = errorMessage.concat(' ', err.global_error.withdraw_by_value[0]);
      }
    }
    this.toastr.error(errorMessage || 'Some error occurred');
    this.submitted = false;
  }
}
