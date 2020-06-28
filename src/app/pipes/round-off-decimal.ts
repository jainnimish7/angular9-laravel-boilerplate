import { Pipe, PipeTransform } from '@angular/core';

/* Add the ordinal to the number. */

@Pipe({ name: 'roundOffDecimal' })

export class RoundOffDecimalPipe implements PipeTransform {
  transform(value: any): any {
    if (value) {
      return parseFloat(value).toFixed(2);
    }
    return '0.00';
  }
}
