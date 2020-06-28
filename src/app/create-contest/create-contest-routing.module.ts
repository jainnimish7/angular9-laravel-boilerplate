import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { CreateContestComponent } from './create-contest.component';
import { AuthGuard } from '../auth-guard/auth.guard';

const routes: Routes = [
  { path: '', component: CreateContestComponent, canActivate: [AuthGuard] },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CreateContestRoutingModule { }
