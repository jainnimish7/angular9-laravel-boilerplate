<?php
/**
 * [time_machine description]
 * Summary :-
 * @param  string $date   [description]
 * @param  string $format [description]
 * @return [type]         [description]
 */
function time_machine($date = 'today', $format = 'Y-m-d H:i:s')
{

  if ($date == "today") {
    if (Config::get('constants.IS_LOCAL_TIME') === true) {
      $back_time = strtotime(BACK_YEAR);
      $dt = date($format, $back_time);
    } else {
      $dt = date($format);
    }
  } else {
    if (is_numeric($date)) {
      $dt = date($format, $date);
    } else {
      if ($date != null) {
        $dt = date($format, strtotime($date));
      } else {
        $dt = "--";
      }
    }
  }

  if (Config::get('constants.ENVIRONMENT') == 'production') {
    return $dt;
  } else {
    $path = Config::get('constants.ROOT_PATH') . 'date_time.php';
    if (file_exists($path)) {
      include($path);
    }

    if (isset($date_time) && $date_time) {
      $dt = date($format, strtotime($date_time));
    }
    return $dt;
  }
}

