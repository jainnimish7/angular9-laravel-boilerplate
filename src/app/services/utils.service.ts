import { DateTime } from 'luxon';

export function encryptPassword(password: string): string {
  return btoa(password);
}

export function range(size: number, startAt = 1) {
  const ans = [];
  for (let i = startAt; i <= size; i++) {
    ans.push(i);
  }
  return ans;
}

export function formatDate(date: any) {
  if (date) {
    if (date[date.length - 3] === '+') {
      return (DateTime.fromSQL(date, { zone: 'utc' }).toLocal());
    }
    return new Date(date);
  }
}

export function setDateformat(date: Date | string) {
  const dateToFormat = (typeof (date) === 'string') ? new Date(date) : date;
  return `${dateToFormat.getFullYear()}-${dateToFormat.getMonth() + 1}-${dateToFormat.getDate()}`;
}

export function formatDateTime(date: Date | string) {
  const dateToFormat = (typeof (date) === 'string') ? new Date(date) : date;
  return `${dateToFormat.getFullYear()}-${dateToFormat.getMonth() + 1}-${dateToFormat.getDate()} ${dateToFormat.getHours()}:${dateToFormat.getMinutes()}:${dateToFormat.getSeconds()}`;
}

export const dateFormatString = 'dd MMM, h:mm a';
export const dayYearFormat = 'dd MMM, y';
export const timeFormat = 'h:mm a';
export const monthDayFormat = 'MMMM d';
export const dayMonthDate = 'EEEE MMM dd';
export const timeOnly = 'h:mm a';
