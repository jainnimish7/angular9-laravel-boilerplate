import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  isBlueBackground = true;
  isDashboardRoute = true;
  isContestRoute = false;

  constructor(private router: Router) {
    router.events.subscribe((route: any) => {
      if (route.url) {
        this.isDashboardRoute = route.url === '/';
        this.isBlueBackground = route.url === '/login' || route.url === '/signup' ||
          route.url === '/forgot-password' || route.url.includes('/reset-password/')
          || route.url.includes('/activate-account/');
        this.isContestRoute = route.url.includes('/contest-setting/');
      }
    });
  }

  ngOnInit() {
  }
}
