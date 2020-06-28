import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'playerFilter', pure: true })
export class PlayerFilterPipe implements PipeTransform {
  transform(items: any[], filter: any): any {
    if (!items || !filter) {
      return items;
    }
    if (filter[filter.key] === 'All') {
      return items;
    }

    // handle for pistion and team
    const filterKey = filter.key === 'position' ? 'position_abbr' : 'team_id';
    const filterValue = filter[filter.key];
    return items.filter(item => item[filterKey] == filterValue);
  }
}
