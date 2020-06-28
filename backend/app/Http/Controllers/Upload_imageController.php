<?php

namespace App\Http\Controllers;

//OR COMPOSER:

require_once("vendor/autoload.php"); //
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class Upload_imageController extends Controller
{

    public function __construct()
    {
	    
    }	

    public function user()
    {
        return Auth::user();
    }


   	public function upload_profile_picture_post($image_path)
    {

    	$space_key = env('DO_SPACES_KEY');
		$space_secret = env('DO_SPACES_SECRET');
		$space_name = env('DO_SPACES_BUCKET');
		$space_region = env('DO_SPACES_REGION');
		$space_uri = env('DO_SPACES_ENDPOINT');



    			$user_id  = $this->user()->user_id;
				$allowed_ext= array('jpg','jpeg','png');
				$file_name =$_FILES['userfile']['name'];
				$fileAry = explode('.',$file_name);
				$file_ext = strtolower(end( $fileAry ));
				$file_size=$_FILES['userfile']['size'];
				$file_tmp= $_FILES['userfile']['tmp_name'];
				$type = pathinfo($file_tmp, PATHINFO_EXTENSION);
				$rawImage = file_get_contents($file_tmp);

				 
				if(!in_array($file_ext, $allowed_ext))
				{
					$response['response_code'] = 500;
					$response['service_name']  = "upload_profile_picture";
					$response['error']       = ' Error in upload image please try after some time';
					$response['message']       = "Error in upload image please try after some time";

					return $response;
					


				}elseif( $file_size > 3000000){
					$response['response_code'] = 500;
					$response['service_name']  = "upload_profile_picture";
					$response['error']       = "Please do not upload more than 3MB";
					$response['message']       = "Please do not upload more than 3MB";

					return $response;
					
				}
				
				$space = new \SpacesConnect($space_key, $space_secret, $space_name, $space_region);
				
				$uploads = "profile_images/{$user_id}/";
				$image_name = $uploads.time().".".$file_ext;
				$image_path = $space_uri."/".$image_name;

				if($space->UploadFile($rawImage, "public", $image_name))
				{
				    $space->DeleteObject($uploads);
				    // return $image_path;					
				}

				$response['data'] = array('image'=>$image_path, 'file_name'=>$file_name);
				$response['response_code'] = 200;
				$response['service_name'] = "upload_profile_picture";
				$response['upload_image_success'] = "upload_image_successfully";

				 return $response; 	

    }


    public function check_folder_exist( $dir , $subdir )
	{
		if( !is_dir( $dir ) )
			@mkdir( $dir , 0777 );

		if( !is_dir( $subdir ) )
			@mkdir( $subdir , 0777 );
	}

}
