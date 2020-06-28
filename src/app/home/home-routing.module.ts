import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { DashboardComponent } from '../dashboard/dashboard.component';
import { AboutUsComponent } from '../about-us/about-us.component';
import { TermsAndConditionsComponent } from '../terms-and-conditions/terms-and-conditions.component';
import { PrivacyPolicyComponent } from '../privacy-policy/privacy-policy.component';
import { LoginComponent } from '../login/login.component';
import { LobbyComponent } from '../lobby/lobby.component';
import { SignupComponent } from '../signup/signup.component';
import { ForgotPasswordComponent } from '../forgot-password/forgot-password.component';
import { FaqComponent } from '../faq/faq.component';

const routes: Routes = [
  { path: '', component: DashboardComponent, runGuardsAndResolvers: 'always' },
  { path: 'terms-and-conditions', component: TermsAndConditionsComponent },
  { path: 'login', component: LoginComponent },
  { path: 'signup', component: SignupComponent },
  { path: 'lobby', component: LobbyComponent },
  { path: 'forgot-password', component: ForgotPasswordComponent },
  { path: 'privacy-policy', component: PrivacyPolicyComponent },
  { path: 'about-us', component: AboutUsComponent },
  { path: 'faq', component: FaqComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class HomeRoutingModule { }
