import { Pipe, PipeTransform } from '@angular/core';

/* Add the ordinal to the number. */

@Pipe({ name: 'roundOff' })

export class RoundOffValuePipe implements PipeTransform {
  transform(value: any): any {
    if (value === '0' || value === '0.00') {
      return '-';
    }
    if (value) {
      // return parseFloat(value);
      return (value);
    }
    return '-';
  }
}
