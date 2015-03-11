<?php
require_once path("base") . "php-amqplib/vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CreateInteractivePDF_Task {

	public function run() {
		$lockFile = path('base') . 'lock/' . __CLASS__ . ".lock";
		$fp = fopen($lockFile, 'r+');
		/* Activate the LOCK_NB option on an LOCK_EX operation */
		if (!flock($fp, LOCK_EX | LOCK_NB)) {
			echo 'Unable to obtain lock';
			exit(-1);
		}

		$connection = new AMQPConnection('localhost', 5672, 'galepress', 'galeprens');
		$channel = $connection->channel();
		$channel->queue_declare('queue_interactivepdf', false, false, false, false);
		$channel->basic_consume('queue_interactivepdf', '', false, true, false, false, array($this, "createInteractivePdf"));
		while (count($channel->callbacks)) {
			$channel->wait();
			ob_flush();
		}
		$channel->close();
		$connection->close();
	}

	public function createInteractivePdf() {
		echo "geldim"; return;
		try {
			$cf = DB::table('ContentFile')
					->where('Interactivity', '=', 1)
					->where(function($query) {
						$query->where_null('HasCreated');
						$query->or_where('HasCreated', '<>', 1);
					})
					->where(function($query) {
						$query->where_null('ErrorCount');
						$query->or_where('ErrorCount', '<', 2);
					})
					->where('StatusID', '=', eStatus::Active)
					->get();

			foreach ($cf as $f) {
				try {
					$this->create($f->ContentFileID, (int) $f->Included);
				} catch (Exception $e) {
					$toEmail = Config::get('custom.admin_email');
					$subject = __('common.task_subject');
					$msg = __('common.task_message', array(
						'task' => '`CreateInteractivePDF`',
						'detail' => $e->getMessage()
							)
					);

					Log::info($msg);

					Bundle::start('messages');

					Message::send(function($m) use($toEmail, $subject, $msg) {
						$m->from(Config::get('custom.mail_email'), Config::get('custom.mail_displayname'));
						$m->to($toEmail);
						$m->subject($subject);
						$m->body($msg);
					});
				}
			}
		} catch (Exception $e) {
			$toEmail = Config::get('custom.admin_email');
			$subject = __('common.task_subject');
			$msg = __('common.task_message', array(
				'task' => '`CreateInteractivePDF`',
				'detail' => $e->getMessage()
					)
			);

			Log::info($msg);

			Bundle::start('messages');

			Message::send(function($m) use($toEmail, $subject, $msg) {
				$m->from(Config::get('custom.mail_email'), Config::get('custom.mail_displayname'));
				$m->to($toEmail);
				$m->subject($subject);
				$m->body($msg);
			});
		}
	}

	public function create($ContentFileID, $Included) {
		try {
			//-----------------------------------------------------------------------------------------------
			$ContentID = (int) ContentFile::find($ContentFileID)->ContentID;
			$ApplicationID = (int) Content::find($ContentID)->ApplicationID;
			$CustomerID = (int) Application::find($ApplicationID)->CustomerID;

			$relativePath = 'files/customer_' . $CustomerID . '/application_' . $ApplicationID . '/content_' . $ContentID . '/file_' . $ContentFileID;
			$path = path('public') . $relativePath;

			//find pdf file
			$pdfFile = '';
			$files = scandir($path);
			foreach ($files as $file) {
				if (is_file($path . '/' . $file) && Common::endsWith($file, '.pdf')) {
					$pdfFile = $file;
					break;
				}
			}
			$fileOriginal = $path . "/" . $pdfFile;

			$baseRelativePath = $relativePath . '/output';
			$basePath = path('public') . $baseRelativePath;
			if (!File::exists($basePath)) {
				File::mkdir($basePath);
			}
			//Couldn't open PDF file '/home/admin/domains/galepress.com/public_html/public/files/customer_1/application_1/content_451/file_740/output/file.pdf' for writing (no such directory)
			$fileOutput = $basePath . "/" . $pdfFile;
			//-----------------------------------------------------------------------------------------------
			$p = new pdflib();
			$p->set_option("license=" . Config::get('custom.pdflib_license'));
			$p->set_option("errorpolicy=return");
			$doc = $p->begin_document($fileOutput, "destination={type=fitwindow} pagelayout=singlepage");
			if ($doc == 0) {
				throw new Exception($p->get_errmsg());
			}

			$p->set_info("Creator", "Galepress");
			$p->set_info("Title", "Galepress Interactive PDF");
			//-----------------------------------------------------------------------------------------------
			//open original document
			$docOriginal = $p->open_pdi_document($fileOriginal, "");
			if ($docOriginal == 0) {
				throw new Exception($p->get_errmsg());
			}

			//get page count
			$pageCount = (int) $p->pcos_get_number($docOriginal, "length:pages");

			for ($page = 0; $page < $pageCount; $page++) {
				//add new page 
				$p->begin_page_ext(10, 10, "");

				//open page in original document
				//$pageOriginal = $p->open_pdi_page($docOriginal, ($page + 1), "cloneboxes");
				$pageOriginal = $p->open_pdi_page($docOriginal, ($page + 1), "pdiusebox=crop");
				if ($pageOriginal == 0) {
					throw new Exception($p->get_errmsg());
				}
				//$p->fit_pdi_page($pageOriginal, 0, 0, "cloneboxes");
				$p->fit_pdi_page($pageOriginal, 0, 0, "adjustpage");

				//close page in original document
				$p->close_pdi_page($pageOriginal);

				$cfp = DB::table('ContentFilePage')
						->where('ContentFileID', '=', $ContentFileID)
						->where('No', '=', $page + 1)
						->where('StatusID', '=', eStatus::Active)
						->first();
				if ($cfp) {
					$width = (float) $cfp->Width;
					$height = (float) $cfp->Height;

					$pc = DB::table('PageComponent')
							->where('ContentFilePageID', '=', $cfp->ContentFilePageID)
							->where('StatusID', '=', eStatus::Active)
							->order_by('No', 'ASC')
							->get();
					foreach ($pc as $c) {
						//get componenet property
						$trigger_x = 0;
						$trigger_y = 0;
						$trigger_w = 60;
						$trigger_h = 60;
						$x = 0;
						$y = 0;
						$w = 0;
						$h = 0;
						$propertyImport = 0;
						$propertyModal = 0;
						$propertyType = 0;
						$propertyUrl = '';
						$propertyPage = 0;
						$propertyText = '';
						$propertyLat = '';
						$propertyLon = '';
						$propertyZoom = 0.09;
						/*
						  $propertyOption = 0;
						  $propertyFilename = '';
						  $propertyVideoinit = '';
						  $propertyHidecontrols = 0;
						  $propertyRestartwhenfinished = 0;
						  $propertyMute = 0;
						  $propertyPosteroption = 0;
						  $propertyPosterimagename = '';
						  $propertyBoxcolor = '';
						  $propertyIconcolor = '';
						  $propertyOpacity = 0;
						  $propertyContent = '';
						 */

						$data = array(
							'preview' => false,
							'baseDirectory' => '',
							'id' => $c->PageComponentID
						);

						$pcp = DB::table('PageComponentProperty')
								->where('PageComponentID', '=', $c->PageComponentID)
								->where('StatusID', '=', eStatus::Active)
								->order_by('PageComponentPropertyID', 'ASC')
								->get();
						foreach ($pcp as $cp) {
							if ($cp->Name == 'trigger-x')
								$trigger_x = (int) $cp->Value;
							elseif ($cp->Name == 'trigger-y')
								$trigger_y = (int) $cp->Value;
							elseif ($cp->Name == 'x')
								$x = (int) $cp->Value;
							elseif ($cp->Name == 'y')
								$y = (int) $cp->Value;
							elseif ($cp->Name == 'w')
								$w = (int) $cp->Value;
							elseif ($cp->Name == 'h')
								$h = (int) $cp->Value;
							elseif ($cp->Name == 'import')
								$propertyImport = (int) $cp->Value;
							elseif ($cp->Name == 'modal')
								$propertyModal = (int) $cp->Value;
							elseif ($cp->Name == 'type')
								$propertyType = (int) $cp->Value;
							elseif ($cp->Name == 'url')
								$propertyUrl = $cp->Value;
							elseif ($cp->Name == 'page')
								$propertyPage = (int) $cp->Value;
							elseif ($cp->Name == 'text')
								$propertyText = $cp->Value;
							elseif ($cp->Name == 'lat')
								$propertyLat = $cp->Value;
							elseif ($cp->Name == 'lon')
								$propertyLon = $cp->Value;
							elseif ($cp->Name == 'zoom')
								$propertyZoom = (float) $cp->Value;


							/*
							  elseif($cp->Name == 'videoinit') $propertyVideoinit = $cp->Value;
							  elseif($cp->Name == 'hidecontrols') $propertyHidecontrols = (int)$cp->Value;
							  elseif($cp->Name == 'restartwhenfinished') $propertyRestartwhenfinished = (int)$cp->Value;
							  elseif($cp->Name == 'mute') $propertyMute = (int)$cp->Value;
							  elseif($cp->Name == 'posteroption') $propertyPosteroption = (int)$cp->Value;
							  elseif($cp->Name == 'posterimagename') $propertyPosterimagename = $cp->Value;
							  elseif($cp->Name == 'boxcolor') $propertyBoxcolor = $cp->Value;
							  elseif($cp->Name == 'iconcolor') $propertyIconcolor = $cp->Value;
							  elseif($cp->Name == 'opacity') $propertyOpacity = (int)$cp->Value;
							  elseif($cp->Name == 'content') $propertyContent = $cp->Value;
							 */

							/*
							  else
							  {
							  $param .= (Str::length($param) > 0 ? '&' : '?').$cp->Name.'='.$cp->Value;
							  }
							 */
							$data = array_merge($data, array($cp->Name => $cp->Value));
						}

						if ($propertyModal == 1) {
							$trigger_w = 52;
							$trigger_h = 52;
						}

						//Log::info('before reverse trigger_y:'.$trigger_y);
						//reverse y
						$y = $height - $y - $h;
						$trigger_y = $height - $trigger_y;
						//$trigger_y = $height - $trigger_y - $trigger_h;

						$component = PageComponent::find($c->PageComponentID)->Component();
						$componentID = $component->ComponentID;
						$componentClass = $component->Class;

						$qs = '';
						$paramQS = array();

						array_push($paramQS, 'componentTypeID=' . $componentID);

						if ($propertyModal == 1) {
							array_push($paramQS, 'modal=1');
						}
						if (count($paramQS) > 0) {
							$qs = '?' . implode("&", $paramQS);
						}

						if ($propertyModal == 1) {
							//$p->set_parameter("SearchPath", path('public')."files/components/".$componentClass);
							//$triggerImage = $p->load_image("auto", "icon.png", "");
							$image_url = path('public') . $data["modaliconname"];
							if (File::exists($image_url) && is_file($image_url)) {
								$image_url = Config::get("custom.url") . "/" . $data["modaliconname"];
							} else {
								$image_url = Config::get("custom.url") . "/files/components/" . $componentClass . "/icon.png";
							}
							$imageData = file_get_contents($image_url);
							if ($imageData == false) {
								throw new Exception("Error: file_get_contents($image_url) failed");
							}
							$p->create_pvf("/pvf/image", $imageData, "");
							$triggerImage = $p->load_image("auto", "/pvf/image", "");
							if ($triggerImage == 0) {
								throw new Exception($p->get_errmsg());
							}
							//$optlist = "boxsize={30 30} position={center} fitmethod=meet matchbox={borderwidth=10 offsetleft=-5 offsetright=5 offsetbottom=-5 offsettop=5 linecap=round linejoin=round strokecolor {rgb 0.0 0.3 0.3}}";
							//$optlist = "boxsize={52 52} position={center} fitmethod=meet";
							//$p->fit_image($triggerImage, $trigger_x, $trigger_y, $optlist);
							$optlist = "boxsize={" . $w . " " . $h . "} position={center} fitmethod=meet";
							$p->fit_image($triggerImage, $x, $y, $optlist);
							$p->close_image($triggerImage);
							$p->delete_pvf("/pvf/image");

							//$x = $trigger_x;
							//$y = $trigger_y;
							//$w = 52;
							//$h = 52;
						}

						if ($componentClass == 'video' || $componentClass == 'audio' || $componentClass == 'animation' || $componentClass == 'tooltip' || $componentClass == 'scroll' || $componentClass == 'slideshow' || $componentClass == 'gal360') {

							/*
							  foreach($data as $var => $val)
							  {
							  Log::info($var." = ".$val);
							  }
							  //2013-04-20 09:33:01 INFO - pcid = 1935
							  //2013-04-20 09:33:01 INFO - option = 1
							  //2013-04-20 09:33:01 INFO - filename = files/customer_1/application_28/content_224/file_22/output/comp_1935/10_20130420093210_files/customer_1/application_28/content_224/file_22/output/comp_1935/10_20130420092710_files/customer_1/application_28/content_224/file_22/output/comp_1935/10_20130420092456_small.mp4
							  //2013-04-20 09:33:01 INFO - url =
							  //2013-04-20 09:33:01 INFO - videoinit = onstart
							  //2013-04-20 09:33:01 INFO - hidecontrols = 1
							  //2013-04-20 09:33:01 INFO - restartwhenfinished = 1
							  //2013-04-20 09:33:01 INFO - mute = 0
							  //2013-04-20 09:33:01 INFO - posteroption = 2
							  //2013-04-20 09:33:01 INFO - posterimagename = 6.jpg
							  //2013-04-20 09:33:01 INFO - x = 132
							  //2013-04-20 09:33:01 INFO - y = 84
							  //2013-04-20 09:33:01 INFO - w = 223
							  //2013-04-20 09:33:01 INFO - h = 183
							 */

							//ylvideo://xxx.com/video.mp4?autostart=X?modal=0
							//ylvideo://localhost/video=001.mp4?autostart=X?modal=0
							/*
							  $bladeTemplate = array(
							  'video' => 'player.xml',
							  'audio' => 'index.html',
							  'tooltip' => 'index.html',
							  'scroll' => 'index.html',
							  'slideshow' => 'index.html',
							  'gal360' => 'threesixty.js'
							  );
							 */

							//create component directory
							$outputPath = $path . '/output';
							$componentPath = $outputPath . '/comp_' . $c->PageComponentID;

							if (!File::exists($componentPath)) {
								File::mkdir($componentPath);
							}

							//extract zip file
							$zipFile = path('public') . 'files/components/' . $componentClass . '/files.zip';

							$zip = new ZipArchive();
							$res = $zip->open($zipFile);
							if ($res === true) {
								$zip->extractTo($componentPath);
								$zip->close();
							}

							//copy file
							if ($componentClass == 'slideshow' || $componentClass == 'gal360') {
								$files = DB::table('PageComponentProperty')
										->where('PageComponentID', '=', $c->PageComponentID)
										->where('Name', '=', 'filename')
										->where('StatusID', '=', eStatus::Active)
										->order_by('PageComponentPropertyID', 'ASC')
										->get();
								$arr = array();
								foreach ($files as $file) {
									array_push($arr, $file->Value);
								}
								$data = array_merge($data, array('files' => $arr));
								//$data = array_merge($data, array('files' => $files));
							}

							if ($componentClass == 'audio') {
								$x = $trigger_x;
								$y = $trigger_y - $trigger_h;
								$w = $trigger_w;
								$h = $trigger_h;
							}

							//video url youtube embed 
							if ($componentClass == 'video' && !(strpos($propertyUrl, 'www.youtube.com/embed') === false)) {

								if (strpos($propertyUrl, '?') !== false) {
									$qs = str_replace('?', '&', $qs);
								}
								$propertyUrl = str_replace("http", "ylweb", $propertyUrl . $qs);
								$action = $p->create_action("URI", "url {" . $propertyUrl . "}");
								$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							} else {
								$content = View::make('interactivity.components.' . $componentClass . '.dynamic', $data)->render();
								File::put($outputPath . '/comp_' . $c->PageComponentID . '.html', $content);
								//File::put($componentPath.'/'.$bladeTemplate[$componentClass], $content);

								$url = 'ylweb://www.galepress.com/files/customer_' . $CustomerID . '/application_' . $ApplicationID . '/content_' . $ContentID . '/file_' . $ContentFileID . '/output/comp_' . $c->PageComponentID . '.html' . $qs;

								if ($Included == 1 || $propertyImport == 1) {
									$url = 'ylweb://localhost/comp_' . $c->PageComponentID . '.html' . $qs;
								}

								$action = $p->create_action("URI", "url {" . $url . "}");
								$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							}
							//video || audio || tooltip || scroll || slideshow || gal360
						} elseif ($componentClass == 'map') {
							//$propertyType
							//$propertyLat
							//$propertyLon
							//$propertyZoom
							$mapType = 'standard';

							if ($propertyType == 2) {
								$mapType = 'hybrid';
							} else if ($propertyType == 3) {
								$mapType = 'satellite';
							}
							//$propertyUrl = str_replace("http", "ylweb", $propertyUrl);
							$zoom = ((100 - ($propertyZoom * 1000)) / 1000);
							$propertyUrl = 'ylmap://' . $mapType . $qs . '&lat=' . $propertyLat . '&lon=' . $propertyLon . '&slat=' . $zoom . '&slon=' . $zoom;
							$action = $p->create_action("URI", "url {" . $propertyUrl . "}");
							$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							//map
						} elseif ($componentClass == 'link') {
							//$propertyType
							//$propertyPage
							//$propertyUrl
							if ($propertyType == 1) {
								//goto page
								$optlist = "destination={page=" . $propertyPage . " type=fixed left=10 top=10 zoom=1}";
								$action = $p->create_action("GOTO", $optlist);
								$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							} elseif ($propertyType == 2) {
								//goto link
								if (strpos($propertyUrl, '?') !== false) {
									$qs = str_replace('?', '&', $qs);
								}
								$action = $p->create_action("URI", "url {" . $propertyUrl . $qs . "}");
								$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							}
							//link
						} elseif ($componentClass == 'webcontent') {
							if (strpos($propertyUrl, '?') !== false) {
								$qs = str_replace('?', '&', $qs);
							}
							$propertyUrl = str_replace("http", "ylweb", $propertyUrl . $qs);
							$action = $p->create_action("URI", "url {" . $propertyUrl . "}");
							$p->create_annotation($x, $y, $x + $w, $y + $h, "Link", "linewidth=0 action {activate $action}");
							//webcontent
						} elseif ($componentClass == 'bookmark') {
							//$propertyText
							$propertyText = pack('H*', 'feff') . mb_convert_encoding($propertyText, 'UTF-16', 'UTF-8');
							$p->create_bookmark($propertyText, "destination={page=" . ($page + 1) . " type=fixed left=" . $trigger_x . " top=" . $trigger_y . " zoom=1}");
							//bookmark
						}
					}
				}

				//end new page
				$p->end_page_ext("");
			}
			//close document
			$p->close_pdi_document($docOriginal);
			//-----------------------------------------------------------------------------------------------
			$p->end_document("");
			//-----------------------------------------------------------------------------------------------
			//create zip archive
			if (File::exists($basePath . '/file.zip')) {
				File::delete($basePath . '/file.zip');
			}

			//Create zip archive
			$zip = new ZipArchive();
			$res = $zip->open($basePath . '/file.zip', ZIPARCHIVE::CREATE);
			if ($res === true) {
				$this->addToZip($zip, $basePath, $Included !== 1);
				$zip->close();
			}
			//-----------------------------------------------------------------------------------------------
			$a = Application::find($ApplicationID);
			$a->Version = (int) $a->Version + 1;
			$a->save();

			$s = Content::find($ContentID);
			$s->Version = (int) $s->Version + 1;
			$s->PdfVersion = (int) $s->PdfVersion + 1;
			$s->save();

			$cf = ContentFile::find($ContentFileID);
			$cf->HasCreated = 1;
			//$cf->ErrorCount = 0;
			//$cf->LastErrorDetail = '';
			$cf->InteractiveFilePath = $baseRelativePath;
			$cf->InteractiveFileName = 'file.zip';
			$cf->InteractiveFileSize = File::size($basePath . '/files.zip');
			$cf->save();
			//-----------------------------------------------------------------------------------------------
		} catch (PDFlibException $e) {
			$err = 'PDFlib exception occurred in starter_block sample: [' . $e->get_errnum() . '] ' . $e->get_apiname() . ': ' . $e->get_errmsg();
			$cf = ContentFile::find($ContentFileID);
			$cf->ErrorCount = (int) $cf->ErrorCount + 1;
			$cf->LastErrorDetail = $err;
			$cf->save();
			throw new Exception($err);
		} catch (Exception $e) {
			$err = $e->getMessage();
			$cf = ContentFile::find($ContentFileID);
			$cf->ErrorCount = (int) $cf->ErrorCount + 1;
			$cf->LastErrorDetail = $err;
			$cf->save();
			throw new Exception($err);
		}
		$p = 0;
	}

	public function addToZip(&$zip, &$basePath, $partial = false) {
		$arrComponentActive = array();
		$arrComponentPassive = array();

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath . "/"), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file) {
			$file = str_replace('\\', '/', $file);

			//Ignore "." and ".." folders
			if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
				continue;

			//Check component activity
			if (preg_match("/\/files\/customer_(\d+)\/application_(\d+)\/content_(\d+)\/file_(\d+)\/output\/comp_(\d+)/", $file, $m)) {
				//$customerID = (int)$m[1];
				//$applicationID = (int)$m[2];
				//$contentID = (int)$m[3];
				//$contentFileID = (int)$m[4];
				$checkComponentID = (int) $m[5];
				//echo $customerID.'-'.$applicationID.'-'.$contentID.'-'.$contentFileID.'-'.$checkComponentID;

				if (in_array($checkComponentID, $arrComponentPassive)) {
					continue;
				}

				if (!in_array($checkComponentID, $arrComponentActive)) {

					$importCount = 0;

					if ($partial) {
						$importCount = DB::table('PageComponentProperty')
								->where('PageComponentID', '=', $checkComponentID)
								->where('Name', '=', 'import')
								->where('Value', '=', 1)
								->where('StatusID', '=', eStatus::Active)
								->count();
					}

					$checkComponentCount = DB::table('PageComponent')
							->where('PageComponentID', '=', $checkComponentID)
							->where('StatusID', '=', eStatus::Active)
							->count();

					if ($importCount == 1) {
						array_push($arrComponentActive, $checkComponentID);
					} elseif (!$partial && $checkComponentCount == 1) {
						array_push($arrComponentActive, $checkComponentID);
					} else {
						array_push($arrComponentPassive, $checkComponentID);
						continue;
					}
				}
			}

			$realFile = realpath($file);
			$relativeFile = str_replace($basePath . '/', '', $realFile);

			if (is_dir($realFile) === true) {
				$zip->addEmptyDir($relativeFile . '/');
			} else if (is_file($realFile) === true) {
				$zip->addFile($realFile, $relativeFile);
			}
		}
	}

}
