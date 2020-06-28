import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'searchName', pure: true })
export class SearchNamePipe implements PipeTransform {
  transform(items: any[], nameSearchTerm: any): any {
    if (!items || !nameSearchTerm) {
      return items;
    }

    return items.filter(i => {
      return (i.full_name.toLowerCase().replace(/\s+/g, ' ').indexOf(nameSearchTerm.toLowerCase().replace(/\s+/g, ' ')) !== -1) ||
        (i.en_full_name.toLowerCase().replace(/\s+/g, ' ').indexOf(nameSearchTerm.toLowerCase().replace(/\s+/g, ' ')) !== -1);
    });
  }
}
