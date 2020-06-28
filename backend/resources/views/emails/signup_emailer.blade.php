@extends('emails.main')
@section('content')
<tr>
  <td style="padding:0 15px 0 30px; ">
    <p style="color:#000; font-size:30px; font-family:Calibri; margin:0px; padding:0; font-weight:bold; line-height:50px; ">Hi <?php echo $username; ?>,</p>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <h3 style="margin:0px; padding:0px; color:#fff; display:block; font-size:22px;">
      Thanks for signing up at <?php echo env('PROJECT_NAME_MAIL_FORMAT') ; ?>.
    </h3>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-weight: bold;font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    Please<a href="<?php echo $link; ?>" style="color:#000; text-shadow: 0 0px 1px rgba(0,0,0,0.8);text-decoration:none; cursor:pointer;font-weight: bold;"> click here</a> to verify your email
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-weight: bold;font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <p style="margin:0px; padding:0px; color:#333333; display:block; font-size:14px;">

    </p>
    <p style="margin:0px; padding:0px; color:#fff; display:block; font-size:14px;font-weight: bold;">
      We are ready for you to join our Adventure.
    </p>
  </td>
</tr>
@endsection



