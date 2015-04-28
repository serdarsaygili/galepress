<?php

/**
 * @property int $ContentID Description
 * @property int $ApplicationID Description
 * @property int $OrderNo Description
 * @property int $Name Description
 * @property int $Detail Description
 * @property int $MonthlyName Description
 * @property int $PublishDate Description
 * @property int $UnpublishDate Description
 * @property int $IsUnpublishActive Description
 * @property int $CategoryID Description
 * @property int $IsProtected Description
 * @property int $Password Description
 * @property int $IsBuyable Description
 * @property int $Price Description
 * @property int $CurrencyID Description
 * @property int $IsMaster Description
 * @property int $Orientation Description
 * @property int $Identifier Description
 * @property int $AutoDownload Description
 * @property int $Approval Description
 * @property int $Blocked Description
 * @property int $Status Description
 * @property int $Version Description
 * @property int $PdfVersion Description
 * @property int $CoverImageVersion Description
 * @property int $TotalFileSize Description
 * @property int $StatusID Description
 * @property int $CreatorUserID Description
 * @property int $DateCreated Description
 * @property int $ProcessUserID Description
 * @property int $ProcessDate Description
 * @property int $ProcessTypeID Description
 */
class Content extends Eloquent {

	public static $timestamps = false;
	public static $table = 'Content';
	public static $key = 'ContentID';

	/*
	  public function Application()
	  {
	  return $this->belongs_to('Application', 'ApplicationID');
	  }

	  public function Category()
	  {
	  return $this->belongs_to('Category', 'CategoryID');
	  }
	 */

	public function Application() {
		return $this->belongs_to('Application', 'ApplicationID')->first();
	}

	public function Currency($languageID) {
		//return $this->belongs_to('GroupCode', 'GroupCodeID')->first();
		$gc = GroupCode::where('GroupCodeID', '=', $this->CurrencyID)->first();
		if ($gc) {
			return $gc->DisplayName($languageID);
		}
		return '';
	}

	public function Files($statusID) {
		return $this->has_many('ContentFile', $this->key())->where('StatusID', '=', $statusID)->get();
	}

	public function ActiveFile() {
		return $this->has_many('ContentFile', $this->key())->where('StatusID', '=', eStatus::Active)->first();
	}

	public function CoverImageFiles($statusID) {
		return $this->has_many('ContentCoverImageFile', $this->key())->where('StatusID', '=', $statusID)->get();
	}

	public function Tags() {
		return $this->has_many('ContentTag', $this->key());
	}

		$currentCategories = array();
		$rows = $this->getCategoryIDSet();
		foreach ($rows as $row) {
			$categoryIDArray = array($row->CategoryID != CATEGORY_GENEL_ID ? $row->CategoryID : "");
			$currentCategories = array_merge($currentCategories, $categoryIDArray);
		}

		$pdfUploaded = (int) Input::get('hdnFileSelected', 0);
		$imageUploaded = (int) Input::get('hdnCoverImageFileSelected', 0);
		if ($this->dirty() || $pdfUploaded || $imageUploaded || $currentCategories != $selectedCategories) {
			$this->Version = (int) $this->Version + 1;
			return TRUE;
		}
		return FALSE;
	}

	public function getCategoryIDSet() {
		return DB::table('ContentCategory')
						->where('ContentID', '=', $this->ContentID)
						->get();
	}

	public function setPassword($password) {
		if (strlen(trim($password)) > 0) {
			$this->Password = Hash::make(Input::get('Password'));
		}
	}

	public function setMaster($isMaster) {
		$this->IsMaster = $isMaster;
		if ($isMaster) {
			//Unset IsProtected & password field due to https://github.com/galepress/gp/issues/7
			$this->IsProtected = 0;
			$this->Password = '';
			$contents = DB::table('Content')->where('ApplicationID', '=', $this->ApplicationID)->get();
			foreach ($contents as $content) {
				//INFO:Added due to https://github.com/galepress/gp/issues/18
				if ((int) $this->ContentID !== (int) $content->ContentID) {
					$a = Content::find($content->ContentID);
					$a->IsMaster = 0;
					$a->Version = (int) $a->Version + 1;
					$a->ProcessUserID = Auth::User()->UserID;
					$a->ProcessDate = new DateTime();
					$a->ProcessTypeID = eProcessTypes::Update;
					$a->save();
				}
			}
		}
	}

	public function updateApplicationVersion() {
		if ($this->dirty()) {
			$a = Application::find($this->ApplicationID);
			if ($a) {
				$a->Version = (int) $a->Version + 1;
				$a->ProcessUserID = Auth::User()->UserID;
				$a->ProcessDate = new DateTime();
				$a->ProcessTypeID = eProcessTypes::Update;
				$a->save();
			}
		}
	}

	public function setCategory($selectedCategories) {
		//content categories
		DB::table('ContentCategory')->where('ContentID', '=', $this->ContentID)->delete();
		foreach ($selectedCategories as $value) {
			//add category
			$cat = new ContentCategory();
			$cat->ContentID = $this->ContentID;
			$cat->CategoryID = (int) $value;
			$cat->save();
		}
	}

	public function processPdf($customerID) {
		if ((int) Input::get('hdnFileSelected', 0) != 1) {
			return (int) DB::table('ContentFile')
							->where('ContentID', '=', $this->ContentID)
							->where('StatusID', '=', eStatus::Active)
							->max('ContentFileID');
		}
		
		$contentFileID = 0;
		$sourceFileName = Input::get('hdnFileName');
		$sourceFilePath = 'files/temp';
		$sourceRealPath = path('public') . $sourceFilePath;
		$sourceFileNameFull = $sourceRealPath . '/' . $sourceFileName;

		$targetFileName = Auth::User()->UserID . '_' . date("YmdHis") . '_' . $sourceFileName;
		$targetFilePath = 'files/customer_' . $customerID . '/application_' . $this->ApplicationID . '/content_' . $this->ContentID;
		$destinationFolder = path('public') . $targetFilePath;
		$targetFileNameFull = $destinationFolder . '/' . $targetFileName;

		if (File::exists($sourceFileNameFull)) {
			if (!File::exists($destinationFolder)) {
				File::mkdir($destinationFolder);
			}

			$this->PdfVersion += 1;
			$this->save();

			$originalImageFileName = pathinfo($sourceFileNameFull, PATHINFO_FILENAME) . IMAGE_ORJ_EXTENSION;
			File::move($sourceFileNameFull, $targetFileNameFull);
			File::move($sourceRealPath . "/" . $originalImageFileName, $destinationFolder . "/" . IMAGE_ORIGINAL . IMAGE_EXTENSION);
			$f = new ContentFile();
			$f->ContentID = $this->ContentID;
			$f->DateAdded = new DateTime();
			//$f->FilePath = '/'.$targetFilePath;
			$f->FilePath = $targetFilePath;
			$f->FileName = $targetFileName;
			//$f->FileName2 = '';
			$f->FileSize = File::size($targetFileNameFull);
			$f->Transferred = (int) Input::get('Transferred', '0');
			$f->StatusID = eStatus::Active;
			$f->CreatorUserID = Auth::User()->UserID;
			$f->DateCreated = new DateTime();
			$f->ProcessUserID = Auth::User()->UserID;
			$f->ProcessDate = new DateTime();
			$f->ProcessTypeID = eProcessTypes::Insert;
			$f->save();

			$contentFileID = $f->ContentFileID;
		}

		return $contentFileID;
	}

	public function processImage($customerID, $contentFileID) {
		if ((int) Input::get('hdnCoverImageFileSelected', 0) != 1) {
			return;
		}
		$sourceFileName = Input::get('hdnCoverImageFileName');
		$sourceFilePath = 'files/temp';
		$sourceRealPath = path('public') . $sourceFilePath;
		$sourceFileNameFull = $sourceRealPath . '/' . $sourceFileName;

		$targetFileName = Auth::User()->UserID . '_' . date("YmdHis") . '_' . $sourceFileName;
		$targetFilePath = 'files/customer_' . $customerID . '/application_' . $this->ApplicationID . '/content_' . $this->ContentID;
		$destinationFolder = path('public') . $targetFilePath;
		$targetFileNameFull = $destinationFolder . '/' . $targetFileName;

		$targetMainFileName = $targetFileName . '_main';
		$targetThumbFileName = $targetFileName . '_thumb';

		if (File::exists($sourceFileNameFull) && is_file($sourceFileNameFull)) {
			if (!File::exists($destinationFolder)) {
				File::mkdir($destinationFolder);
			}
			sleep(5);
			File::move($sourceFileNameFull, $targetFileNameFull);
			if ((int) Input::get('hdnFileSelected', 0) == 0) {
				File::copy($targetFileNameFull, $destinationFolder . '/' . IMAGE_ORIGINAL . IMAGE_EXTENSION);
			}
			$pictureInfoSet = array();
			$pictureInfoSet[] = array("width" => 110, "height" => 157, "imageName" => $targetMainFileName);
			$pictureInfoSet[] = array("width" => 468, "height" => 667, "imageName" => $targetThumbFileName);
			foreach ($pictureInfoSet as $pInfo) {
				imageClass::cropImage($targetFileNameFull, $destinationFolder, $pInfo["width"], $pInfo["height"], $pInfo["imageName"], FALSE);
			}

			$cropSet = Crop::get();
			$cropSet instanceof Crop;
			$sourceFile = $destinationFolder . "/" . IMAGE_ORIGINAL . IMAGE_EXTENSION;
			foreach ($cropSet as $crop) {
				//create neccessary image versions
				imageClass::cropImage($sourceFile, $destinationFolder, $crop->Width, $crop->Height);
			}

			$this->CoverImageVersion += 1;
			$this->save();

			$c = new ContentCoverImageFile();
			$c->ContentFileID = $contentFileID;
			$c->DateAdded = new DateTime();
			$c->FilePath = $targetFilePath;
			$c->SourceFileName = $targetFileName;
			$c->FileName = $targetMainFileName . IMAGE_EXTENSION;
			$c->FileName2 = $targetThumbFileName . IMAGE_EXTENSION;
			$c->FileSize = File::size($destinationFolder . "/" . $targetMainFileName . ".jpg");
			$c->StatusID = eStatus::Active;
			$c->CreatorUserID = Auth::User()->UserID;
			$c->DateCreated = new DateTime();
			$c->ProcessUserID = Auth::User()->UserID;
			$c->ProcessDate = new DateTime();
			$c->ProcessTypeID = eProcessTypes::Insert;
			$c->save();
			Cookie::put(SHOW_IMAGE_CROP, SHOW_IMAGE_CROP);
		}
	}

}
