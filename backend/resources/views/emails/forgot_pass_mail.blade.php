@extends('emails.main')
@section('content')
<tr>
  <td style="padding:0 15px 0 30px; ">
    <h3 style="color:#000; font-size:30px; font-family:Calibri; margin:0px; padding:0; font-weight:bold; line-height:50px; ">Hello <?php echo $username; ?>,</h3>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <p style="margin:0px; padding:0px; color:#fff; display:block; font-size:22px;">
      You've requested instructions to reset your
      current password. Simply click on the button
      below and you will be redirect to our
      application to create your new password.
    </p>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-weight: bold;font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <a href="<?php echo $link; ?>" style="color:#000; text-shadow: 0 0px 1px rgba(0,0,0,0.8);text-decoration:none; cursor:pointer;font-weight: bold;">
    Create new password 
    </a>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-weight: bold;font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <p style="margin:0px; padding:0px; color:#333333; display:block; font-size:14px;">

    </p>
    <p style="margin:0px; padding:0px; color:#fff; display:block; font-size:14px;font-weight: bold;">
      If you did not request to change your current
      password, please ignore this email and
      contact us immediately.
    </p>
  </td>
</tr>
<tr>
  <td style="padding:0 30px 10px 30px; font-weight: bold;font-family:Arial, Helvetica, sans-serif; font-size:14px;">
    <p style="margin:0px; padding:0px; color:#333333; display:block; font-size:14px;">

    </p>
    <p style="margin:0px; padding:0px; color:#fff; display:block; font-size:14px;font-weight: bold;">
      kindly,
    </p>
  </td>
</tr>
@endsection