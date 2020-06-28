import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { PipeModule } from '../../../pipes/pipes.module';

import { HeaderComponent } from '../../header/header.component';
import { FooterComponent } from '../../footer/footer.component';
// import { MyProfileComponent } from '../../../my-profile/my-profile.component';
import { LobbyComponent } from '../../../lobby/lobby.component';
import { AuthGuard } from '../../../auth-guard/auth.guard';
import { LoaderComponent } from '../../loader/loader.component';
import { LoaderService } from '../../loader/loader.service';

const routes: Routes = [
  { path: 'lobby', component: LobbyComponent },
];

@NgModule({
  imports: [
    BsDatepickerModule.forRoot(),
    CommonModule,
    FormsModule,
    PipeModule,
    ReactiveFormsModule,
    [RouterModule.forChild(routes)],
  ],
  declarations: [
    FooterComponent,
    HeaderComponent,
    LoaderComponent,
    LobbyComponent,
  ],
  providers: [
    LoaderService
  ],
  exports: [
    HeaderComponent,
    FooterComponent,
    LoaderComponent,
  ]
})
export class PostAuthenticationModule { }
