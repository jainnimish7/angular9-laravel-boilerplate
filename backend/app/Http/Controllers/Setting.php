<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class Setting extends Controller
{
    //

    public function change_date_time_post(Request $request)
	{
		$date = '';
		if ($request->post("date"))
		{
			$date = date("Y-m-d H:i:s", strtotime($request->post("date")));
		}

		$date_time = '';

		if ( $date )
			$date_time = $date;

		$path = base_path().'/date_time.php';

		$data = '<?php $date_time = "'.$date_time.'";';

	     Storage::put($path, $data);

	     return response()->json(['response_code'=>200,'service_name' => 'date_time','message'=>"Update date time successfully"]);

	}


}
