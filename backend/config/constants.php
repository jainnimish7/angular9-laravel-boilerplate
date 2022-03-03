<?php
//file : app/config/constants.php

return [
  'IS_LOCAL_TIME' => 'true',
  'BACK_YEAR' => '0 month',
  'ENVIRONMENT' => 'local',
  'AD_IMAGE_DIR' => 'advertisement',
  'DEFAULT_TIME_ZONE' => 'UTC',
  'PAYPAL_MODE' => (env('APP_ENV') === 'production') ? 'production' : 'sandbox',
  'PAYPAL_CLIENT_ID' => (env('APP_ENV') === 'production') ? '' : 'AQGkOO0-hPcm0mSSEpmMXlSTK5KMOkVY0Z2-6L-kgv5NdkDw3sKin-8KpfIynpbKfKKp7x-Afrgub3xL',
  'PAYPAL_CLIENT_SECRET' => (env('APP_ENV') === 'production') ? '' : 'EO-fJi4itSBsTifLUoG0aXg4z1yVd5TzgbHTWEMZp5rNNVnPZQGjdHqSB1jYXLY2gyct6bj5ugLojizM',
  'MIN_WITHDRAW_AMOUNT' => 10,
];
