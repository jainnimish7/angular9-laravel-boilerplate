import { AbstractControl, FormControl } from '@angular/forms';

export class CustomValidators {
  // check if password and confirmPassword fields match
  static MatchPassword(AC: AbstractControl) {
    const password = AC.get('password').value; // to get value in input tag
    const confirmPassword = AC.get('confirmPassword').value; // to get value in input tag
    if (password !== confirmPassword) {
      return { MatchPassword: true };
    } else {
      return null;
    }
  }

  static validUsername() {
    return (control: FormControl) => {
      // Regex validation - must start with alphabet, does not contain any character except underscore.
      const pattern = new RegExp(/^[a-zA-Z]([_]?[a-zA-Z0-9]+)*$/);
      const res = pattern.test(control.value);
      return res ? null : { invalid: true };
    };
  }

  // validating array of emails
  static validateEmail() {
    return (control: FormControl) => {
      const pattern = new RegExp('^([a-z0-9\\+_\\-]+)(\\.[a-z0-9\\+_\\-]+)*@([a-z0-9\\-]+\\.)+[a-z]{2,6}$', 'i');
      let validEmail = true;
      if (control.value !== null) {
        const emails = control.value.replace(/\s/g, '').split(',').filter((mail: any) => mail.trim());
        for (const email of emails) {
          if (email && !pattern.test(email)) {
            validEmail = false;
          }
        }
      }
      return validEmail ? null : { invalid_email: true };
    };
  }
}
