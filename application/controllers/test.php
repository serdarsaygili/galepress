<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author Detay
 */
class Test_Controller extends Base_Controller{
	public $restful = true;
	
	public function __construct() {
		parent::__construct();
	}

	public function get_index() {
		$tmp = __('route.crop_image')->get("tr");
		echo $tmp;
		//return View::make('test.googlemaps');
	}
	
	public function get_image() {
		$cropSet = Crop::get();
		$cropSet instanceof Crop;
//		foreach($cropSet as $crop) {
//			echo $crop->CropID; 
//		}
//		$applicationID = (int) Input::get('applicationID', 20);
//		$app = Application::find($applicationID);
		$contentID = (int)Input::get("contentID" , 0);
		$contentID = 1893;
		$contentFile = DB::table('ContentFile')
					->where('ContentID', '=', $contentID)
					->where('StatusID', '=', eStatus::Active)
					->order_by('ContentFileID', 'DESC')->first();
		$contentFile instanceof ContentFile;
		$ccif = DB::table('ContentCoverImageFile')
				->where('ContentFileID', '=', $contentFile->ContentFileID)
				->where('StatusID', '=', eStatus::Active)
				->order_by('ContentCoverImageFileID', 'DESC')->first();
		$ccif instanceof ContentCoverImageFile;
		//bu contentin imageini bulalim....
		//calculate the absolute path of the source image
		$imagePath = $contentFile->FilePath . "/" . $ccif->SourceFileName;
		$imageInfo = new imageInfoEx($imagePath);
		$data = array();
		$data['cropSet'] = $cropSet;
		$data['imageInfo'] = $imageInfo;
		return View::make('test.image', $data);
	}
	
	public function post_image() {
//		var_dump($_POST);
//		exit;
		
		
		$xCoordinateSet = Input::get("xCoordinateSet");
		$yCoordinateSet = Input::get("yCoordinateSet");
		$heightSet = Input::get("heightSet");
		$widthSet = Input::get("widthSet");
		$cropIDSet = Input::get("cropIDSet");
		$contentID = (int)Input::get("contentID", 0);
		$contentID = 1893;

		$contentFile = DB::table('ContentFile')
					->where('ContentID', '=', $contentID)
					->where('StatusID', '=', eStatus::Active)
					->order_by('ContentFileID', 'DESC')->first();
		$contentFile instanceof ContentFile;
		$ccif = DB::table('ContentCoverImageFile')
				->where('ContentFileID', '=', $contentFile->ContentFileID)
				->where('StatusID', '=', eStatus::Active)
				->order_by('ContentCoverImageFileID', 'DESC')->first();
		$ccif instanceof ContentCoverImageFile;
		//bu contentin imageini bulalim....
		//calculate the absolute path of the source image
		$sourceImagePath = $contentFile->FilePath . "/" . $ccif->SourceFileName;
		$imageInfo = new imageInfoEx($sourceImagePath);
		
		
		for($i = 0; $i < count($xCoordinateSet); $i++) {
			$crop = Crop::find($cropIDSet[$i]);
			if(!$crop) {
				continue;
			}
			$crop instanceof Crop;
			$im = new Imagick($imageInfo->absolutePath);
			$im->cropimage($widthSet[$i], $heightSet[$i], $xCoordinateSet[$i], $yCoordinateSet[$i]);
			$im->resizeImage($crop->Width,$crop->Height,Imagick::FILTER_LANCZOS,1, TRUE);
			$im->writeImage(path('public') .  $contentFile->FilePath . "/" . IMAGE_CROPPED_NAME . "_" . $crop->Width . "x" . $crop->Height . ".jpg" );
			$im->destroy();
		}
	}
}
