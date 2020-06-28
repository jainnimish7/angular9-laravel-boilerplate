import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuthGuard } from './auth-guard/auth.guard';
import { ConfirmAccountComponent } from './confirm-account/confirm-account.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { NotFoundComponent } from './not-found/not-found.component';

const routes: Routes = [
  { path: '', loadChildren: () => import('./home/home.module').then(m => m.HomeModule) },
  { path: '', redirectTo: '/', pathMatch: 'full' },
  {
    path: 'my-profile',
    loadChildren: () => import('./my-profile/my-account.module').then(m =>
      m.MyAccountModule)
  },
  // {
  //   path: 'create-contest',
  //   loadChildren: () => import('./create-contest/create-contest.module').then(m =>
  //     m.CreateContestModule)
  // },
  { path: 'activate-account/:token', component: ConfirmAccountComponent },
  { path: 'reset-password/:token', component: ResetPasswordComponent },
  { path: '**', component: NotFoundComponent },
];

@NgModule({
  // onSameUrlNavigation option used to simulate reload on home page when clicking on logo
  imports: [RouterModule.forRoot(routes, { onSameUrlNavigation: 'reload' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
