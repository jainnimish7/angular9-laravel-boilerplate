import { Component, OnInit, Input } from '@angular/core';
import { ToastrService } from 'ngx-toastr';

declare const $: any;

@Component({
  selector: 'app-referral',
  templateUrl: './referral.component.html',
  styleUrls: ['./referral.component.scss']
})

export class ReferralComponent {
  @Input() userUniqueId: any;
  constructor(private toastr: ToastrService) { }

  copyMessage(val: string) {
    const selBox = document.createElement('textarea');
    selBox.style.position = 'fixed';
    selBox.style.left = '0';
    selBox.style.top = '0';
    selBox.style.opacity = '0';
    selBox.value = val;
    document.body.appendChild(selBox);
    selBox.focus();
    selBox.select();
    document.execCommand('copy');
    document.body.removeChild(selBox);
    this.toastr.success('Referral Code Copied');
  }
}
