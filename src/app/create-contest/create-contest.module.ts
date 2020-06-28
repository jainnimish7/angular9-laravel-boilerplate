import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PostAuthenticationModule } from '../shared/layouts/post-authentication/post-authentication.module';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { PipeModule } from '../pipes/pipes.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CreateContestRoutingModule } from './create-contest-routing.module';
import { CreateContestComponent } from './create-contest.component';

@NgModule({
  declarations: [
    CreateContestComponent,
  ],
  imports: [
    BsDatepickerModule.forRoot(),
    CommonModule,
    PipeModule,
    FormsModule,
    PostAuthenticationModule,
    CreateContestRoutingModule,
    ReactiveFormsModule,
    FormsModule,
  ]
})

export class CreateContestModule { }
