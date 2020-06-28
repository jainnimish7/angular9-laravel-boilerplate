import { Component, OnInit } from '@angular/core';
import { LoaderService } from '../shared/loader/loader.service';
import { FormBuilder } from '@angular/forms';
import { ContestService } from 'src/app/services/contest.service';
import { ToastrService } from 'ngx-toastr';
import { Router } from '@angular/router';

@Component({
  selector: 'app-create-contest',
  templateUrl: './create-contest.component.html',
  styleUrls: ['./create-contest.component.scss']
})

export class CreateContestComponent implements OnInit {

  constructor(
    private loaderService: LoaderService,
    private formBuilder: FormBuilder,
    private contestService: ContestService,
    private toastr: ToastrService,
  ) { }

  ngOnInit() {
  }
}
