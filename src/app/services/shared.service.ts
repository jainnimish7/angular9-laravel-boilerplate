import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject } from 'rxjs';
import { map } from 'rxjs/operators';
import { DateTime } from 'luxon';
import { AddOrdinalPipe } from '../pipes/add-ordinal.pipe';
import { environment } from '../../environments/environment';
// import { AuthService, FacebookLoginProvider, GoogleLoginProvider, SocialUser } from 'angularx-social-login';

@Injectable()
export class SharedService {
  timeFormat = 'MMMM dd, y hh:mm a';
  dateFormat = 'MMMM dd, y';
  private userObject = new BehaviorSubject({});
  currentUser = this.userObject.asObservable();

  constructor(private addOrdinal: AddOrdinalPipe, private http: HttpClient) { }

  updateUser(user: any) {
    this.userObject.next(user);
  }

  socialLogin(type) {
    // let provider = GoogleLoginProvider.PROVIDER_ID;
    // if (type === 'facebook') {
    //   provider = FacebookLoginProvider.PROVIDER_ID;
    // }
    // return this.socialAuthService.signIn(provider)
    //   .then((userDetail) => {
    //     const data = {
    //       email: userDetail.email,
    //       first_name: userDetail.firstName,
    //       last_name: userDetail.lastName,
    //       social_id: userDetail.id,
    //       social_type: type,
    //       image: userDetail.photoUrl.replace('normal', 'large'),
    //       device_type: 1
    //     };
    //     return data;
    //   });
  }

  // trim initial name if name length is greater than 16
  trimInitialName(name: string) {
    if (name.length > 16) {
      let newName = '';
      const arr = name.split(' ');
      for (let index = 0; index < arr.length - 1; index++) {
        const element = arr[index];
        newName += element[0].toUpperCase() + '. ';
      }
      return (newName += arr[arr.length - 1]);
    }
    return name;
  }

  // calculating total prize pool
  calculatePoolAmount(prize_pool, site_rake) {
    if (prize_pool && site_rake) {
      const finalizeAmount = (this.stringToFixedNumber(prize_pool) * this.stringToFixedNumber(site_rake) / 100);
      return this.stringToFixedNumber(prize_pool) - finalizeAmount;
    }
    return 0;
  }

  // consolation prize data
  consolationPrizeData(prize, index) {
    const objLength = this.sizeOfObject(prize.value);
    const limit = this.addOrdinal.transform(index + 1);
    const data = (objLength > 1) ? limit.concat('-', this.addOrdinal.transform(index + objLength)) : limit;
    return data + ': ';
  }

  getExpiryTime(unit, duration) {
    const time = unit === 'HOURS' ? 3600000 : 60000;
    return new Date(Date.now() + parseInt(duration, 10) * time).getTime().toString();
  }

  // converting string to float/integer
  stringToFixedNumber(num: any) {
    // console.log(parseFloat(num), 'number');
    return parseFloat(num);
  }

  // truncate string length when greater than 9
  truncateStr(str) {
    if (str && str.length > 9) {
      return str.substr(0, 9) + '..';
    }
    return str;
  }

  // finding size of object
  sizeOfObject(obj: object) {
    let size = 0;
    for (const key in obj) {
      if (obj.hasOwnProperty(key)) {
        size++;
      }
    }
    return size;
  }

  getTimeDifferenceInMinutes(date: string) {
    return DateTime.fromFormat(date + ' +0', 'yyyy-MM-dd HH:mm:ss Z').diffNow(['minutes']).minutes;
  }

  isLessThan24Hours(modifiedDate) {
    const lastDate = modifiedDate.replace(' ', 'T') + 'Z';
    const timeDiff = Date.now() - new Date(lastDate).getTime();
    const daysDiff = timeDiff / (1000 * 3600 * 24);
    if (daysDiff <= 1) {
      return true;
    }
    return false;
  }

  // Converting to fixed number and its string.
  convertToFixedString(val) {
    return parseInt(val, 10).toString();
  }

  verifyWeeklyLimit(limit, that) {
    if (that.daily.amount && that.monthly.amount) {
      return (+limit > +that.daily.amount && +limit < +that.monthly.amount);
    }
    if (that.daily.amount) {
      return (+limit > +that.daily.amount);
    }
    if (that.monthly.amount) {
      return (+limit < +that.monthly.amount);
    }
    return true;
  }

  verifyDailyLimit(limit, that) {
    if (that.weekly.amount) {
      return (+limit < +that.weekly.amount);
    } else if (that.monthly.amount) {
      return (+limit < +that.monthly.amount);
    }
    return true;
  }

  verifyMonthlyLimit(limit, that) {
    if (that.weekly.amount) {
      return (+limit > +that.weekly.amount);
    } else if (that.daily.amount) {
      return (+limit > +that.daily.amount);
    }
    return true;
  }

  public scoringRules(params) {
    const url = `${environment.API_URL}` + '/common/get_scoring_rules';
    return this.http.post(url, params).pipe(map(response => {
      return response;
    }));
  }

}
