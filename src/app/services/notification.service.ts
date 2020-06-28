import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class NotificationService {

  constructor(private http: HttpClient) { }

  // fetching all notifications
  fetchNotifications(params: object) {
    return this.http.post(`${environment.API_URL}` + '/notifications/get_all_notifications', params);
  }

  // mark unread notifications as read
  markUnreadNotificationsAsRead(params: any) {
    return this.http.post(`${environment.API_URL}` + '/notifications/is_read', params);
  }
}

