<?php
/**
 * @property int $BannerID Description
 * @property int $ApplicationID Description
 * @property int $OrderNo Description
 * @property int $ImagePublicPath Description
 * @property int $ImageLocalPath Description
 * @property int $TargetUrl Description
 * @property int $TargetContent Description
 * @property int $Description Description
 * @property int $Version Version
 * @property int $Status Description
 * @property int $StatusID Description
 * @property int $created_at Description
 * @property int $updated_at Description
 */
class Banner extends Eloquent{
	public static $table = 'Banner';
	public static $key = 'BannerID';
	
	/**
	 * 
	 * @param type $bannerID
	 * @return Banner
	 */
	public static function find($bannerID) {
		return Banner::where(self::$key, '=', $bannerID)->where("StatusID", '=', eStatus::Active)->first();
	}
	
	/**
	 * 
	 * @param type $applicationID
	 * @return Banner
	 */
	public static function getAppBanner($applicationID) {
		return Banner::where("ApplicationID", "=", $applicationID)
				->where("StatusID", "=", eStatus::Active)
				->order_by("OrderNo", "Desc")
				->get();
	}
	
	/**
	 * 
	 * @param Application $application
	 * @return type
	 */
	public function processImage($application) {
		if((int)Input::get("hdnImageFileSelected") != 1) {
			return;
		}
		
		
		$tmpFileName = Input::get("hdnImageFileName");
		$tmpFilePath = path('public') . PATH_TEMP_FILE . '/' . $tmpFileName;
		$destinationFolder = path('public') . 'files/customer_' . $application->CustomerID . '/application_' . $application->ApplicationID . '/banner/';
		$sourcePicturePath = $destinationFolder . Auth::User()->UserID . '_' . date("YmdHis") . '_' . $tmpFileName;
		if(!is_file($tmpFilePath)) {
			return;
		}
		
		if (!File::exists($destinationFolder)) {
			File::mkdir($destinationFolder);
		}
		
		File::move($tmpFilePath, $sourcePicturePath);
		
		$pictureInfoSet = array();
		$pictureInfoSet[] = array("width" => 740, "height" => 320, "imageName" => $this->BannerID);
		foreach($pictureInfoSet as $pInfo) {
			imageClass::cropImage($sourcePicturePath, $destinationFolder, $pInfo["width"], $pInfo["height"], $pInfo["imageName"], FALSE);
		}
		
	}
	
	public function save($updateAppVersion = TRUE) {
		if(!$this->dirty()) {
			return;
		}
		
		if($this->BannerID == 0) {
			$this->StatusID = eStatus::Active;
		}
		$this->Version = (int)$this->Version + 1;
		if($updateAppVersion) {
			$App = Application::find($this->ApplicationID);
			if($App) {
				$App->incrementAppVersion();
			}
		}
		parent::save();
	}
	
	public function statusText() {
		return __('common.banners_list_status' . $this->Status);
	}
	
	public function getImagePath($application) {
		return '/files/customer_' . $application->CustomerID . '/application_' . $application->ApplicationID . '/banner/' . $this->BannerID . IMAGE_EXTENSION;
	}
}
