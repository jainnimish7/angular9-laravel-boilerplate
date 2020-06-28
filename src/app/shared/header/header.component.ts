import { AfterViewInit, Component, OnInit, HostListener } from '@angular/core';
import { AuthenticationService } from '../../services/authentication.service';
import { Router } from '@angular/router';
import { UserService } from 'src/app/services/user.service';
import { LoaderService } from '../loader/loader.service';
import { SharedService } from 'src/app/services/shared.service';
import { ToastrService } from 'ngx-toastr';

declare const $: any;
declare const jQuery: any;

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent implements OnInit, AfterViewInit {
  user: any;
  showBalance: false;
  timer: any;
  duration: string;
  unit: string;
  mobileView = false;
  innerWidth: any;
  istoggleactive = true;
  shutoutContestId = null;
  constructor(public authService: AuthenticationService, private router: Router,
              private toastr: ToastrService, private userService: UserService,
              private loaderService: LoaderService, public sharedService: SharedService) {
    this.sharedService.currentUser.subscribe((obj: any) => {
      if (this.sharedService.sizeOfObject(obj)) {
        this.user = obj;
        this.user.total_balance = +obj.balance + +obj.winning_balance;
        this.duration = obj.duration;
        this.unit = obj.unit;
      }
    });
  }

  @HostListener('window:resize', ['$event'])
  onResize(event?) {
    this.innerWidth = window.innerWidth;
    if (this.innerWidth < 768) {
      this.mobileView = true;
    } else {
      this.mobileView = false;
    }
  }

  ngOnInit() {
    this.authService.isAuthenticated.subscribe(res => {
      if (res) {
        this.updateHeader();
      } else {
        // clearInterval(this.timer);
      }
    });
    this.onResize();
    this.istoggleactive = true;
  }

  updateHeader() {
    this.loaderService.display(true);
    this.userService.getUserData().subscribe((usr: any) => {
      this.sharedService.updateUser(usr.data.user_profile);
      this.loaderService.display(false);
    }, err => {
      this.loaderService.display(false);
    });
  }

  ngAfterViewInit() {
  }

  setActiveClass() {
    $('.responsive-menu').on('click', 'li', function() {
      $('.responsive-menu li.active').removeClass('active');
      $(this).addClass('active');
    });
  }

  navigateToHome() {
    $('.responsive-menu li.active').removeClass('active');
    this.router.navigate(['/']);
    $('#home-button').addClass('active');
  }

  toggleTimer() {
    $('.clock').toggle();
    $('.timer').toggleClass('orange-clock');
  }

  setExpiryTime() {
    if (this.duration && this.unit) {
      const currentTime = this.sharedService.getExpiryTime(this.unit, this.duration);
      sessionStorage.setItem('expiredTime', currentTime);
    }
  }

  // Before opening modal, checking if user is already logged in or not.
  openLoginOrSignup(modalName: string) {
    const token = localStorage.getItem('AuthToken') || '';
    if (token.length > 0) {
      this.updateHeader();
      this.router.navigate(['/my-profile']);
      this.toastr.success('You are already logged in.');
    } else {
      $('#' + modalName).modal('show');
    }
  }

  continueSession() {
    sessionStorage.removeItem('expiredTime');
    this.setExpiryTime();
  }

  logout() {
    this.authService.logout().pipe()
      .subscribe(() => {
        this.router.navigate(['/']);
        this.toastr.success('Logged out Successfully');
        this.istoggleactive = true;
      }, error => {
        console.error('Error while logging out!', error);
      });
  }
  onMouseOver() {
    if (this.istoggleactive) {
      this.istoggleactive = false;
      (function($) { // Begin jQuery
        $(function() { // DOM ready
          // If a link has a dropdown, add sub menu toggle.
          $('nav ul li a:not(:only-child)').click(function(e) {
            $(this).siblings('.nav-dropdown').toggle();
            // Close one dropdown when selecting another
            $('.nav-dropdown').not($(this).siblings()).hide();
            e.stopPropagation();
          });
          // Clicking away from dropdown will remove the dropdown class
          $('html').click(function() {
            $('.nav-dropdown').hide();
          });
          // Toggle open and close nav styles on click

          // Hamburger to X toggle
          $('#nav-toggle').on('click', function() {
            this.classList.toggle('active');
          });
        }); // end DOM ready
        $('#nav-toggle').click(function() {
          $('nav ul.responsive-menu').slideToggle();
        });
      })(jQuery); // end jQuery
      $('.responsive-menu a').click(function() {
        $('#nav-toggle').removeClass('active');
        // $(this).closest('.responsive-menu').prev().dropdown('toggle');
        $('nav ul.responsive-menu').slideToggle();
      });
      $('#home-button').addClass('active');
    }
  }
}
