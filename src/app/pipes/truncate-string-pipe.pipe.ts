import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'truncateString'
})
export class TruncateStringPipePipe implements PipeTransform {

  transform(value: any, args: any): any {
    return value.length > args.limit ? value.substr(0, args.limit) + '...' : value;
  }

}
