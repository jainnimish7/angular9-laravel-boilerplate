import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DashboardService } from '../services/dashboard.service';

@Component({
  selector: 'app-about-us',
  templateUrl: './about-us.component.html',
  styleUrls: ['./about-us.component.scss']
})
export class AboutUsComponent implements OnInit {
  contactForm: FormGroup;
  formDetailsSent = false;
  submitted = false;

  constructor(private formBuilder: FormBuilder, private dashboardService: DashboardService) { }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.contactForm = this.formBuilder.group({
      email: ['', [Validators.required,
      Validators.pattern(new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i'))]],
      name: [''],
      message: ['', [Validators.required, Validators.minLength(10)]]
    });
  }

  // getter for easy access to form fields
  get f() {
    return this.contactForm.controls;
  }

  onSubmit() {
    this.submitted = true;
    // stop here if form is invalid
    if (this.contactForm.invalid) {
      return;
    }
    const data = {
      email: this.f.email.value.toLowerCase(),
      name: this.f.name.value,
      message: this.f.message.value,
    };

    this.dashboardService.submitContactUsForm(data).pipe()
      .subscribe((response: any) => {
        this.formDetailsSent = true;
        console.log(response);
      });
  }

}
