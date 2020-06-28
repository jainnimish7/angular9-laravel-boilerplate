import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, Params } from '@angular/router';
import { UserService } from '../services/user.service';
import { ToastrService } from 'ngx-toastr';
import { AuthenticationService } from '../services/authentication.service';
declare const $: any;

@Component({
  selector: 'app-confirm-account',
  templateUrl: './confirm-account.component.html',
  styleUrls: ['./confirm-account.component.scss']
})
export class ConfirmAccountComponent implements OnInit {
  token: string;
  verified = false;

  constructor(private route: ActivatedRoute, private userService: UserService,
              private router: Router, private toastr: ToastrService,
              private authService: AuthenticationService) { }

  ngOnInit() {
    if (this.authService.isUserAuthenticated) {
      this.router.navigate(['/my-profile']);
      this.toastr.success('You are already logged in.');
    } else {
      this.route.params.forEach((params: Params) => {
        this.token = params.token;
        if (this.token) {
          this.userService.verifyAccount(this.token)
            .subscribe(() => {
              this.verified = true;
              this.toastr.success('Your account is successfully confirmed.');
            }, err => {
              if (err.status !== 401) {
                this.router.navigate(['/']);
                this.toastr.error(err.error.global_error || 'This link is invalid.');
              }
            });
        }
      });
    }
  }

  // Closing sign up modal and reset the form details
  openSignInModal() {
    $('#login-modal').modal('show');
  }
}
