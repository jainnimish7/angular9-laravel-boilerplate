import { Pipe, PipeTransform } from '@angular/core';

/* Add the ordinal to the number. */

@Pipe({ name: 'ordinal' })

export class AddOrdinalPipe implements PipeTransform {
  transform(value: number): any {
    if (isNaN(value) || value < 1) {
      return value;
    } else {
      const lastDigit = Number(value);
      if (lastDigit === 1) {
        return value + 'st';
      } else if (lastDigit === 2) {
        return value + 'nd';
      } else if (lastDigit === 3) {
        return value + 'rd';
      } else if (lastDigit > 3) {
        return value + 'th';
      } else {
        return value + 'th';
      }
    }
  }
}
