import { Component, OnInit } from '@angular/core';
import { LoaderService } from './loader.service';

@Component({
  selector: 'app-loader',
  styleUrls: ['./loader.scss'],
  template: `
    <div class="loading-image" *ngIf="showLoader">
    </div>
  `
})
export class LoaderComponent implements OnInit {
  showLoader = false;

  constructor(private loaderService: LoaderService) {
  }

  ngOnInit() {
    this.loaderService.status.subscribe((val: boolean) => {
      this.showLoader = val;
    });
  }

}
