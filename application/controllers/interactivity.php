<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Interactivity_Controller extends Base_Controller
{

    public $restful = true;

    public function get_preview()
    {
        $componentID = Input::get('componentid');
        $componentName = Input::get('componentname');
        $c = Component::where('Class', '=', $componentName)->where('StatusID', '=', eStatus::Active)->count();
        if ($c > 0) {
            $ids = Input::get('compid');
            if ($ids !== null) {
                $data = array(
                    'preview' => true,
                    'baseDirectory' => Config::get("custom.url") . '/files/components/' . $componentName . '/',
                    'id' => ''
                );

                foreach ($ids as $id) {
                    if ((int)$componentID == (int)$id) {
                        $clientPageComponentID = (int)Input::get('comp-' . $id . '-pcid', '0');
                        $postedData = Input::get();

                        foreach ($postedData as $name => $value) {
                            if (Common::startsWith($name, 'comp-' . $id)) {
                                $name = str_replace('comp-' . $id . '-', "", $name);

                                if ($name !== "id" && $name !== "process" && $name !== "fileselected" && $name !== "posterimageselected" && $name !== "modaliconselected") {
                                    if (($name == 'file' || $name == 'filename' || $name == 'filename2') && is_array($value)) {
                                        //var_dump($value);
                                        //return;

                                        $files = array();

                                        foreach ($value as $v) {
                                            //var_dump($v);

                                            $pcp = DB::table('PageComponentProperty')
                                                ->where('PageComponentID', '=', $clientPageComponentID)
                                                ->where('Name', '=', $name)
                                                ->where('Value', 'LIKE', '%' . $v . '%')
                                                ->where('StatusID', '=', 1)
                                                ->first();

                                            if ($pcp) {
                                                //$files = array_merge($files, array($name => $pcp->Value));
                                                array_push($files, $pcp->Value);
                                            } else {
                                                $val = 'files/temp/' . $v;

                                                //$files = array_merge($files, array($name => $val));
                                                array_push($files, $val);
                                            }
                                        }

                                        //var_dump($files);
                                        //return;

                                        $data = array_merge($data, array('files' => $files));
                                    } elseif ($name == 'file' || $name == 'filename' || $name == 'filename2' || $name == 'posterimagename' || $name == 'modaliconname') {
                                        $pcp = DB::table('PageComponentProperty')
                                            ->where('PageComponentID', '=', $clientPageComponentID)
                                            ->where('Name', '=', $name)
                                            ->where('StatusID', '=', 1)
                                            ->first();
                                        if ($pcp) {
                                            if (Common::endsWith($pcp->Value, $value)) {
                                                $data = array_merge($data, array($name => $pcp->Value));
                                            } else {
                                                $val = 'files/temp/' . $value;
                                                $data = array_merge($data, array($name => $val));
                                            }
                                        } else {
                                            $val = 'files/temp/' . $value;
                                            $data = array_merge($data, array($name => $val));
                                        }
                                    } elseif ($name == 'url' && !Common::startsWith($value, 'http://') && !Common::startsWith($value, 'https://') && !empty($value)) {
                                        $value = 'http://' . $value;

                                        $data = array_merge($data, array($name => $value));
                                    } else {
                                        $data = array_merge($data, array($name => $value));
                                    }
                                }
                            }
                        }
                        break;
                    }
                }


                //var_dump($data);
                //return;

                if (isset($data['modal'])) {
                    if ((int)$data['modal'] == 1) {
                        $image_url = path('public') . $data["modaliconname"];
                        if (File::exists($image_url) && is_file($image_url)) {
                            $image_url = "/" . $data["modaliconname"];
                        } else {
                            $image_url = "/files/components/" . $componentName . "/icon.png";
                        }
                        // height="52"
                        return '<html><head></head><body style="margin:0px; padding:0px;"><img src="' . $image_url . '" width="100%"></body></html>';
                        //return '<html><head></head><body style="margin:0px; padding:0px;"><img src="/files/components/'.$componentName.'/icon.png"></body></html>';
                    }
                }

                if ($componentName == 'video' || $componentName == 'audio' || $componentName == 'animation' || $componentName == 'tooltip' || $componentName == 'scroll' || $componentName == 'slideshow' || $componentName == 'gal360') {
                    $url = '';
                    if (isset($data['url'])) {
                        $url = $data['url'];
                    }
                    if (!(strpos($url, 'www.youtube.com/watch') === false)) {
                        $parts = parse_url($url);
                        parse_str($parts['query'], $query);
                        $data['url'] = 'http://www.youtube.com/embed/' . $query['v'];
                        return Redirect::to($data['url']);
                    }
                    if (!(strpos($url, 'www.youtube.com/embed') === false)) {
                        return Redirect::to($data['url']);
                    }
                    // var_dump($data);

                    return View::make('interactivity.components.' . $componentName . '.dynamic', $data);
                } elseif ($componentName == 'map') {
                    $type = 'roadmap';
                    if ((int)$data['type'] == 2) {
                        //hybrid
                        $type = 'satellite';
                    } elseif ((int)$data['type'] == 3) {
                        //satellite
                        $type = 'satellite';
                    }
                    /*
                      --------------------------------------------------------------------
                      http://stackoverflow.com/questions/9356724/google-map-api-zoom-range
                      --------------------------------------------------------------------
                      Google Maps basics
                      Zoom Level - zoom
                      0 - 19
                      0 lowest zoom (whole world)
                      19 highest zoom (individual buildings, if available) Retrieve current zoom level using mapObject.getZoom()
                      --------------------------------------------------------------------
                      0.01 = 10
                      0.02 = 20
                      .
                      .
                      --------------------------------------------------------------------
                     */
                    $zoom = 1000 * (float)$data['zoom'];
                    $z = (19 * $zoom / 100);
                    //$z = $data['zoom'];
                    //return Redirect::to('http://maps.google.com/?ie=UTF8&ll='.$data['lat'].','.$data['lon'].'&spn=0.029332,0.061455&t='.$type.'&z='.$z.'&output=embed');
                    return Redirect::to('https://www.google.com/maps/embed/v1/view?maptype=' . $type . '&zoom=' . $z . '&center=' . $data['lat'] . ',' . $data['lon'] . '&key=AIzaSyBGyONehKJ2jCRF9YekkvWDXOI_UVxeVE4');
                    //return Redirect::to('https://www.google.com/maps/embed/v1/view?maptype='.$type.'&zoom='.$z.'&center='.$data['lat'].','.$data['lon'].'&key=AIzaSyCj2v2727lBWLeXbgM_Hw_VEQgzjDgb8KY');
                } elseif ($componentName == 'link') {
                    return '';
                } elseif ($componentName == 'webcontent') {
                    //https://www.google.com/maps/preview?ll=40.995374,29.108083&z=15&t=m&hl=tr-TR&gl=US&mapclient=embed&cid=9770296505447935381
                    //https://www.google.com/maps/preview?ll=40.995374,29.108083&z=15&t=m&hl=tr-TR&gl=US&mapclient=embed&cid=9770296505447935381
                    //return $data['url'];
                    //return Redirect::to('https://www.google.com/maps/place/Teknik+Yap%C4%B1+Holding/@40.995374,29.108083,17z/data=!3m1!4b1!4m2!3m1!1s0x0:0x879710e80d76ed95?hl=en-US&key=AIzaSyCj2v2727lBWLeXbgM_Hw_VEQgzjDgb8KY');
                    //return Redirect::to('https://www.google.com/maps/embed/v1/view?maptype=satellite&zoom=1&center=59,-123&key=AIzaSyBGyONehKJ2jCRF9YekkvWDXOI_UVxeVE4');
                    //return Response::make('<iframe src="'.$data['url'].'&output=embed'.'"></iframe>', 200);
                    //return Response::make('<script type="text/javascript">setTimeout(function () { alert("emre"); document.location.href = "'.$data['url'].'"; }, 500);</script>', 200);
                    //return Response::make('<script type="text/javascript">setTimeout(function() { alert("emre"); document.location.href = "'.$data['url'].'&output=embed&key=AIzaSyBGyONehKJ2jCRF9YekkvWDXOI_UVxeVE4'.'"; }, 500);</script><script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>', 200);
                    //return Response::make('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>', 200);
                    //return Redirect::to('https://www.google.com/maps/embed/v1/view?maptype=satellite&center=40.995374,29.108083&key=AIzaSyBGyONehKJ2jCRF9YekkvWDXOI_UVxeVE4');
                    //return Response::make('<a href="'.$data['url'].'" rel="noreferrer" id="autoclick">go</a><script1>document.getElementById("autoclick").click();</script1>', 200);
                    //return Response::make('<html><head><meta http-equiv="refresh" content="3;url='.$data['url'].'"/></head><body>Please wait, while redirecting...<script type="text/javascript"></body></html>', 200);
                    return Redirect::to($data['url']);
                } elseif ($componentName == 'bookmark') {
                    return '';
                }
            }
        }
        return 'error';
    }

    public function get_show($contentFileID)
    {
        set_time_limit(3000);
        $currentUser = Auth::User();
        $ContentID = (int)ContentFile::find($contentFileID)->ContentID;
        $ApplicationID = (int)Content::find($ContentID)->ApplicationID;

        if (!Common::CheckContentOwnership($ContentID)) {
            $data = array(
                'errmsg' => __('error.unauthorized_user_attempt')
            );
            return View::make('interactivity.error', $data);
        }

        if (!Common::AuthInteractivity($ApplicationID)) {
            $data = array(
                'errmsg' => __('error.auth_interactivity')
            );
            return View::make('interactivity.error', $data);
        }

        $cf = ContentFile::find($contentFileID);

        $oldContentFileID = 0;

        if ((int)$cf->Transferred == 1) {
            $oldContentFileID = (int)DB::table('ContentFile')
                ->where('ContentFileID', '<', $contentFileID)
                ->where('ContentID', '=', $cf->ContentID)
                ->where('Interactivity', '=', 1)
                ->where('StatusID', '=', eStatus::Active)
                ->order_by('ContentFileID', 'DESC')
                ->take(1)
                ->only('ContentFileID');
        }

        $cfp = DB::table('ContentFilePage')
            ->where('ContentFileID', '=', $cf->ContentFileID)
            ->where('StatusID', '=', eStatus::Active);

        //ilk kez aciliyor!
        if ($cfp->count() == 0) {
            ContentFile::makeContentInteractive($ContentID, $contentFileID, $oldContentFileID);
        }

        $cfp = DB::table('ContentFilePage')
            ->where('ContentFileID', '=', $cf->ContentFileID)
            ->where('StatusID', '=', eStatus::Active)
            ->get();

        $data = array(
            'ContentID' => $cf->ContentID,
            'ContentFileID' => $cf->ContentFileID,
            'included' => (int)$cf->Included,
            'filename' => $cf->FileName,
            'pages' => $cfp
        );
        return View::make('interactivity.master', $data)
            ->nest('header', 'interactivity.header', $data)
            ->nest('sidebar', 'interactivity.sidebar', $data)
            ->nest('footer', 'interactivity.footer', $data);
    }

    public function get_fb($applicationID)
    {
        //return $applicationID;
        $search = Input::get('search', '');
        $cats = Input::get('cat', '');
        $where = '';

        if (is_array($cats)) {
            $arrCategory = array();
            foreach ($cats as $cat) {
                array_push($arrCategory, (int)$cat);
            }
            $where .= ' AND o.`ContentID` IN (SELECT ContentID FROM ContentCategory WHERE CategoryID IN (' . implode(',', $arrCategory) . '))';
        }

        if (Str::length($search) > 0) {
            $search = str_replace("'", "", $search);
            $where .= ' AND o.`Name` LIKE \'%' . $search . '%\'';
        }
        $contentFileSQL = 'SELECT ContentFileID FROM ContentFile WHERE ContentID=o.ContentID AND StatusID=1';
        $sql = '' .
            'SELECT ' .
            'c.CustomerID, ' .
            'c.CustomerName, ' .
            'a.ApplicationID, ' .
            'a.Name AS ApplicationName, ' .
            'o.Name, ' .
            'o.Detail, ' .
            '(SELECT CONCAT(FilePath, \'/\', FileName) FROM ContentCoverImageFile WHERE ContentFileID IN (' . $contentFileSQL . ') AND StatusID=1 ORDER BY ContentCoverImageFileID DESC LIMIT 1) AS CoverImageFile, ' .
            'o.ContentID ' .
            'FROM `Customer` AS c ' .
            'INNER JOIN `Application` AS a ON a.CustomerID=c.CustomerID AND a.ApplicationID=' . (int)$applicationID . ' AND a.StatusID=1 ' .
            'INNER JOIN `Content` AS o ON o.ApplicationID=a.ApplicationID' . $where . ' AND IFNULL(o.Blocked, 0)=0 AND o.Status=1 AND IsProtected=0 AND (SELECT COUNT(*) FROM ContentFilePage WHERE ContentFileID IN (' . $contentFileSQL . ') AND StatusID=1) > 0 AND o.StatusID=1 ' .
            'WHERE c.StatusID=1';
        //var_dump($sql);
        $contents = DB::table(DB::raw('(' . $sql . ') t'))->get();

        $sql = '' .
            'SELECT * ' .
            'FROM Category ' .
            'WHERE CategoryID IN (SELECT CategoryID FROM `ContentCategory` WHERE ContentID IN (SELECT ContentID FROM (' . $sql . ') t)) AND StatusID=1 ' .
            'ORDER BY `Name` ASC';
        $categories = DB::table(DB::raw('(' . $sql . ') t'))->get();
        //$categories = Category::where('ApplicationID', '=', (int)$applicationID)->where('StatusID', '=', eStatus::Active)->order_by('Name', 'ASC')->get();

        $data = array(
            'filterSearch' => $search,
            'filterCat' => $cats,
            'cat' => $categories,
            'contents' => $contents
        );
        return View::make('flipbook.index', $data);
    }

    //POST
    public function post_check()
    {
        try {
            $url = Input::get('url');
            $connectable = false;
            $handle = curl_init($url);
            if ($handle !== false) {
                curl_setopt($handle, CURLOPT_HEADER, true);
                curl_setopt($handle, CURLOPT_FAILONERROR, true);
                curl_setopt($handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($handle, CURLOPT_NOBODY, true);
                curl_setopt($handle, CURLOPT_TIMEOUT, 10);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
                $connectable = curl_exec($handle);
                curl_close($handle);
                if (!$connectable) {
                    //eger server CURLOPT_NOBODY desteklemiyorsa
                    $handle2 = curl_init($url);
                    curl_setopt($handle2, CURLOPT_HEADER, true);
                    curl_setopt($handle2, CURLOPT_FAILONERROR, true);
                    curl_setopt($handle2, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                    curl_setopt($handle2, CURLOPT_TIMEOUT, 10);
                    curl_setopt($handle2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle2, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($handle2, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
                    $connectable = curl_exec($handle2);
                }
            }
            if ($connectable) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return "success=" . base64_encode("false") . "&errmsg=" . base64_encode($e->getMessage());
        }
    }

    public function post_save()
    {
        try {
            $i = 1;
            //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
            $currentUser = Auth::User();

            $included = (int)Input::get('included');
            $contentFileID = (int)Input::get('contentfileid');
            $contentID = (int)ContentFile::find($contentFileID)->ContentID;
            $applicationID = (int)Content::find($contentID)->ApplicationID;
            $customerID = (int)Application::find($applicationID)->CustomerID;

            if (!Common::CheckContentOwnership($contentID)) {
                throw new Exception(__('error.unauthorized_user_attempt'));
            }

            if (!Common::AuthInteractivity($applicationID)) {
                throw new Exception(__('error.auth_interactivity'));
            }

            //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;

            DB::transaction(/**
             * @throws Exception
             */
                function () use ($currentUser, $customerID, $applicationID, $contentID, $contentFileID, $included, $i) {
                    $closing = Input::get('closing');
                    $pageNo = (int)Input::get('pageno');
                    $ids = (array)Input::get('compid');
                    //find current page id
                    $ContentFilePageID = 0;

                    $cfp = DB::table('ContentFilePage')
                        ->where('ContentFileID', '=', $contentFileID)
                        ->where('No', '=', $pageNo)
                        ->where('StatusID', '=', eStatus::Active)
                        ->first();
                    if ($cfp) {
                        $ContentFilePageID = (int)$cfp->ContentFilePageID;
                    }

                    if ($closing == "true") {
                        $cf = ContentFile::find($contentFileID);
                        $cf->Interactivity = 1;
                        $cf->HasCreated = 0;
                        $cf->ErrorCount = 0;
                        $cf->InteractiveFilePath = '';
                        $cf->InteractiveFileName = '';
                        $cf->InteractiveFileName2 = '';
                        $cf->InteractiveFileSize = 0;
                        $cf->Included = ($included == 1 ? 1 : 0);
                        $cf->ProcessUserID = $currentUser->UserID;
                        $cf->ProcessDate = new DateTime();
                        $cf->ProcessTypeID = eProcessTypes::Update;
                        $cf->save();
                    } else {
                        $cf = ContentFile::find($contentFileID);
                        $cf->Included = ($included == 1 ? 1 : 0);
                        $cf->ProcessUserID = $currentUser->UserID;
                        $cf->ProcessDate = new DateTime();
                        $cf->ProcessTypeID = eProcessTypes::Update;
                        $cf->save();
                    }
                    //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
                    foreach ($ids as $id) {
                        //echo 'ids -- ' . 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
                            $clientComponentID = (int)Input::get('comp-' . $id . '-id', '0');
                            $clientPageComponentID = (int)Input::get('comp-' . $id . '-pcid', '0');
                            $clientProcess = Input::get('comp-' . $id . '-process', '');

                            if ($clientProcess == 'new' || $clientProcess == 'loaded') {
                                $tPageComponentExists = false;
                                $tPageComponentID = 0;

                                if ($clientProcess == 'loaded' && $clientPageComponentID > 0) {
                                    $tPageComponentExists = true;
                                    $tPageComponentID = $clientPageComponentID;
                                } else {
                                    $current = DB::table('PageComponent')
                                        ->where('ContentFilePageID', '=', $ContentFilePageID)
                                        ->where('No', '=', $id)
                                        ->where('StatusID', '=', eStatus::Active)
                                        ->first();
                                    if ($current) {
                                        $tPageComponentExists = true;
                                        $tPageComponentID = $current->PageComponentID;
                                    }
                                }

                                if (!$tPageComponentExists) {
                                    $pc = new PageComponent();
                                } else {
                                    $pc = PageComponent::find($tPageComponentID);

                                    if ($ContentFilePageID != (int)$pc->ContentFilePageID) {
                                        throw new Exception("Unauthorized user attempt");
                                    }
                                }
                                $pc->ContentFilePageID = $ContentFilePageID;
                                $pc->ComponentID = $clientComponentID;
                                $pc->No = $id;
                                $pc->StatusID = eStatus::Active;
                                if (!$tPageComponentExists) {
                                    $pc->CreatorUserID = $currentUser->UserID;
                                    $pc->DateCreated = new DateTime();
                                }
                                $pc->ProcessUserID = $currentUser->UserID;
                                $pc->ProcessDate = new DateTime();
                                if (!$tPageComponentExists) {
                                    $pc->ProcessTypeID = eProcessTypes::Insert;
                                } else {
                                    $pc->ProcessTypeID = eProcessTypes::Update;
                                }
                                $pc->save();

                                $pageComponentID = $pc->PageComponentID;

                                if ($tPageComponentExists) {
                                    DB::table('PageComponentProperty')
                                        ->where('PageComponentID', 'IN', DB::raw('(SELECT `PageComponentID` FROM `PageComponent` WHERE `PageComponentID`=' . $pageComponentID . ' AND `ContentFilePageID`=' . $ContentFilePageID . ' AND `StatusID`=1)'))
                                        ->where('StatusID', '=', eStatus::Active)
                                        ->update(
                                            array(
                                                'StatusID' => eStatus::Deleted,
                                                'ProcessUserID' => $currentUser->UserID,
                                                'ProcessDate' => new DateTime(),
                                                'ProcessTypeID' => eProcessTypes::Update
                                            )
                                        );
                                }

                                $postedData = Input::get();
                                foreach ($postedData as $name => $value) {
                                    if (Common::startsWith($name, 'comp-' . $id)) {

                                        $name = str_replace('comp-' . $id . '-', "", $name);

                                        if ($name !== "id" && $name !== "process" && $name !== "fileselected" && $name !== "posterimageselected" && $name !== "modaliconselected") {
                                            //slideshow || gallery360
                                            if (($name == 'file' || $name == 'filename' || $name == 'filename2') && is_array($value)) {
                                                $index = 1;

                                                foreach ($value as $v) {
                                                    if (Str::length($v) > 0) {
                                                        $sourcePath = 'files/temp';
                                                        $sourcePathFull = path('public') . $sourcePath;
                                                        $sourceFile = $v;
                                                        $sourceFileNameFull = $sourcePathFull . '/' . $sourceFile;

                                                        $targetPath = 'files/customer_' . $customerID . '/application_' . $applicationID . '/content_' . $contentID . '/file_' . $contentFileID . '/output/comp_' . $pageComponentID;
                                                        $targetPathFull = path('public') . $targetPath;
                                                        $targetFile = $currentUser->UserID . '_' . date("YmdHis") . '_' . $v;
                                                        //360
                                                        if ($clientComponentID == 9) {
                                                            $targetFile = ($index < 10 ? '0' . $index : '' . $index) . '.jpg';
                                                        }
                                                        $targetFileNameFull = $targetPathFull . '/' . $targetFile;

                                                        if (!File::exists($targetPathFull)) {
                                                            File::mkdir($targetPathFull);
                                                        }

                                                        if (File::exists($sourceFileNameFull)) {
                                                            File::move($sourceFileNameFull, $targetFileNameFull);
                                                            $v = $targetPath . '/' . $targetFile;
                                                        } else {
                                                            $oldValue = DB::table('PageComponentProperty')
                                                                ->where('PageComponentID', '=', $pc->PageComponentID)
                                                                ->where('Name', '=', $name)
                                                                ->where('Value', 'LIKE', '%' . $v)
                                                                ->where('StatusID', '=', eStatus::Deleted)
                                                                ->order_by('PageComponentPropertyID', 'DESC')
                                                                ->first(array('Value'));
                                                            if ($oldValue) {
                                                                $v = $oldValue->Value;
                                                            } else {
                                                                $v = $targetPath . '/' . $v;
                                                            }
                                                            //TODO:kaydete bastiktan sonra ikinci kez kaydete basilirsa veriler bozuluyor !!!
                                                            //$v = $targetPath.'/'.$v;
                                                        }

                                                        $pcp = new PageComponentProperty();
                                                        $pcp->PageComponentID = $pc->PageComponentID;
                                                        $pcp->Name = $name;
                                                        $pcp->Value = $v;
                                                        $pcp->StatusID = eStatus::Active;
                                                        $pcp->CreatorUserID = $currentUser->UserID;
                                                        $pcp->DateCreated = new DateTime();
                                                        $pcp->ProcessUserID = $currentUser->UserID;
                                                        $pcp->ProcessDate = new DateTime();
                                                        $pcp->ProcessTypeID = eProcessTypes::Insert;
                                                        $pcp->save();

                                                        $index = $index + 1;
                                                    }
                                                }
                                            } else {
                                                if (($name == 'file' || $name == 'filename' || $name == 'filename2' || $name == 'posterimagename' || $name == 'modaliconname') && Str::length($value) > 0) {
                                                    $sourcePath = 'files/temp';
                                                    $sourcePathFull = path('public') . $sourcePath;
                                                    $sourceFile = $value;
                                                    $sourceFileNameFull = $sourcePathFull . '/' . $sourceFile;

                                                    $targetPath = 'files/customer_' . $customerID . '/application_' . $applicationID . '/content_' . $contentID . '/file_' . $contentFileID . '/output/comp_' . $pageComponentID;
                                                    $targetPathFull = path('public') . $targetPath;
                                                    $targetFile = $currentUser->UserID . '_' . date("YmdHis") . '_' . $value;
                                                    $targetFileNameFull = $targetPathFull . '/' . $targetFile;

                                                    if (!File::exists($targetPathFull)) {
                                                        File::mkdir($targetPathFull);
                                                    }

                                                    if (File::exists($sourceFileNameFull)) {
                                                        File::move($sourceFileNameFull, $targetFileNameFull);
                                                        $value = $targetPath . '/' . $targetFile;
                                                    } else {
                                                        $oldValue = DB::table('PageComponentProperty')
                                                            ->where('PageComponentID', '=', $pc->PageComponentID)
                                                            ->where('Name', '=', $name)
                                                            ->where('StatusID', '=', eStatus::Deleted)
                                                            ->order_by('PageComponentPropertyID', 'DESC')
                                                            ->first(array('Value'));

                                                        if ($oldValue) {
                                                            $value = $oldValue->Value;
                                                        } else {
                                                            $value = $targetPath . '/' . $value;
                                                        }
                                                        //TODO:kaydete bastiktan sonra ikinci kez kaydete basilirsa veriler bozuluyor !!!
                                                        //$value = $targetPath.'/'.$value;
                                                    }
                                                }

                                                if ($name == 'url' && !Common::startsWith($value, 'http://') && !Common::startsWith($value, 'https://') && !empty($value)) {
                                                    $value = 'http://' . $value;
                                                }
                                                $value = str_replace("www.youtube.com/watch?v=", "www.youtube.com/embed/", $value);

                                                $pcp = new PageComponentProperty();
                                                $pcp->PageComponentID = $pc->PageComponentID;
                                                $pcp->Name = $name;
                                                $pcp->Value = $value;
                                                $pcp->StatusID = eStatus::Active;
                                                $pcp->CreatorUserID = $currentUser->UserID;
                                                $pcp->DateCreated = new DateTime();
                                                $pcp->ProcessUserID = $currentUser->UserID;
                                                $pcp->ProcessDate = new DateTime();
                                                $pcp->ProcessTypeID = eProcessTypes::Insert;
                                                $pcp->save();
                                            }
                                        }
                                    }
                                }
                            } elseif ($clientProcess == 'removed' && $clientPageComponentID > 0) {
                                DB::table('PageComponentProperty')
                                    ->where('PageComponentID', 'IN', DB::raw('(SELECT `PageComponentID` FROM `PageComponent` WHERE `PageComponentID`=' . $clientPageComponentID . ' AND `ContentFilePageID`=' . $ContentFilePageID . ' AND `StatusID`=1)'))
                                    ->where('StatusID', '=', eStatus::Active)
                                    ->update(
                                        array(
                                            'StatusID' => eStatus::Deleted,
                                            'ProcessUserID' => $currentUser->UserID,
                                            'ProcessDate' => new DateTime(),
                                            'ProcessTypeID' => eProcessTypes::Update
                                        )
                                    );

                                DB::table('PageComponent')
                                    ->where('PageComponentID', '=', $clientPageComponentID)
                                    ->where('ContentFilePageID', '=', $ContentFilePageID)
                                    ->where('StatusID', '=', eStatus::Active)
                                    ->update(
                                        array(
                                            'StatusID' => eStatus::Deleted,
                                            'ProcessUserID' => $currentUser->UserID,
                                            'ProcessDate' => new DateTime(),
                                            'ProcessTypeID' => eProcessTypes::Update
                                        )
                                    );

                                //TODO:Delete current file
                            }
                        }

                    //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
                });
            //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
            if (Laravel\Request::env() == ENV_LIVE && Input::get('closing') == "true") {
                interactivityQueue::trigger();
            }
            //echo 'breakPoint: ' . $i++ . " -- " . microtime(true), PHP_EOL;
            return "success=" . base64_encode("true");
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return "success=" . base64_encode("false") . "&errmsg=" . base64_encode($e->getMessage());
        }
    }

    public function post_transfer()
    {
        //return "success=".base64_encode("false");
        try {
            $currentUser = Auth::User();

            $pageFrom = (int)Input::get('from', '0');
            $pageTo = (int)Input::get('to', '0');
            $componentID = (int)Input::get('componentid', '0');
            $contentFileID = (int)Input::get('contentfileid', '0');
            $contentID = (int)ContentFile::find($contentFileID)->ContentID;
            $applicationID = (int)Content::find($contentID)->ApplicationID;
            $customerID = (int)Application::find($applicationID)->CustomerID;

            if (!Common::CheckContentOwnership($contentID)) {
                throw new Exception(__('error.unauthorized_user_attempt'));
            }

            if (!Common::AuthInteractivity($applicationID)) {
                throw new Exception(__('error.auth_interactivity'));
            }

            DB::transaction(function () use ($currentUser, $customerID, $applicationID, $contentID, $contentFileID, $componentID, $pageFrom, $pageTo) {
                $contentFilePageIDFrom = 0;
                $cfp = DB::table('ContentFilePage')
                    ->where('ContentFileID', '=', $contentFileID)
                    ->where('No', '=', $pageFrom)
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();
                if ($cfp) {
                    $contentFilePageIDFrom = (int)$cfp->ContentFilePageID;
                }

                $contentFilePageIDTo = 0;
                $cfp = DB::table('ContentFilePage')
                    ->where('ContentFileID', '=', $contentFileID)
                    ->where('No', '=', $pageTo)
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();
                if ($cfp) {
                    $contentFilePageIDTo = (int)$cfp->ContentFilePageID;
                }

                $cnt = (int)DB::table('PageComponent')->where('ContentFilePageID', '=', $contentFilePageIDFrom)->where('StatusID', '=', eStatus::Active)->count();
                if ($cnt == 0) {
                    throw new Exception(__('interactivity.transfer_error_insufficient'));
                }

                if ($componentID > 0) {
                    DB::table('PageComponent')
                        ->where('ContentFilePageID', '=', $contentFilePageIDFrom)
                        ->where('No', '=', $componentID)
                        ->where('StatusID', '=', eStatus::Active)
                        ->update(array(
                                'ContentFilePageID' => $contentFilePageIDTo,
                                'ProcessUserID' => $currentUser->UserID,
                                'ProcessDate' => new DateTime(),
                                'ProcessTypeID' => eProcessTypes::Update
                            )
                        );
                } else {
                    DB::table('PageComponent')
                        ->where('ContentFilePageID', '=', $contentFilePageIDFrom)
                        ->where('StatusID', '=', eStatus::Active)
                        ->update(array(
                                'ContentFilePageID' => $contentFilePageIDTo,
                                'ProcessUserID' => $currentUser->UserID,
                                'ProcessDate' => new DateTime(),
                                'ProcessTypeID' => eProcessTypes::Update
                            )
                        );
                }

                //From
                $componentNo = 1;
                $pageComponents = DB::table('PageComponent')
                    ->where('ContentFilePageID', '=', $contentFilePageIDFrom)
                    ->where('StatusID', '=', eStatus::Active)
                    ->order_by('PageComponentID', 'ASC')
                    ->get();
                foreach ($pageComponents as $component) {
                    DB::table('PageComponent')
                        ->where('PageComponentID', '=', $component->PageComponentID)
                        ->update(array(
                                'No' => $componentNo,
                                'ProcessUserID' => $currentUser->UserID,
                                'ProcessDate' => new DateTime(),
                                'ProcessTypeID' => eProcessTypes::Update
                            )
                        );
                    $componentNo += 1;
                }

                //To
                $componentNo = 1;
                $pageComponents = DB::table('PageComponent')
                    ->where('ContentFilePageID', '=', $contentFilePageIDTo)
                    ->where('StatusID', '=', eStatus::Active)
                    ->order_by('PageComponentID', 'ASC')
                    ->get();
                foreach ($pageComponents as $component) {
                    DB::table('PageComponent')
                        ->where('PageComponentID', '=', $component->PageComponentID)
                        ->update(array(
                                'No' => $componentNo,
                                'ProcessUserID' => $currentUser->UserID,
                                'ProcessDate' => new DateTime(),
                                'ProcessTypeID' => eProcessTypes::Update
                            )
                        );
                    $componentNo += 1;
                }
            });
            return "success=" . base64_encode("true");
        } catch (Exception $e) {
            return "success=" . base64_encode("false") . "&errmsg=" . base64_encode($e->getMessage());
        }
    }

    public function post_refreshtree()
    {
        //return "success=".base64_encode("false");
        try {
            $currentUser = Auth::User();

            $contentFileID = (int)Input::get('contentfileid', '0');
            $contentID = (int)ContentFile::find($contentFileID)->ContentID;
            $applicationID = (int)Content::find($contentID)->ApplicationID;
            $customerID = (int)Application::find($applicationID)->CustomerID;

            if (!Common::CheckContentOwnership($contentID)) {
                throw new Exception(__('error.unauthorized_user_attempt'));
            }

            if (!Common::AuthInteractivity($applicationID)) {
                throw new Exception(__('error.auth_interactivity'));
            }
            $data = array(
                'ContentFileID' => $contentFileID
            );
            $html = View::make('interactivity.tree', $data)->render();
            return "success=" . base64_encode("true") . "&html=" . base64_encode($html);
        } catch (Exception $e) {
            return "success=" . base64_encode("false") . "&errmsg=" . base64_encode($e->getMessage());
        }
    }

    public function post_upload()
    {
        //$file = Input::file('Filedata');
        //$filePath = path('public').'files/temp';
        //$fileName = File::name($file['name']);
        //$fileExt = File::extension($file['name']);
        //$tempFile = $fileName.'_'.Str::random(20).'.'.$fileExt;

        $type = Input::get('type');
        $element = Input::get('element');

        $options = array();
        if ($type == 'uploadvideofile') {
            $options = array(
                'upload_dir' => path('public') . 'files/temp/',
                'upload_url' => URL::base() . '/files/temp/',
                'param_name' => $element,
                'accept_file_types' => '/\.(mp4)$/i'
            );
        } else if ($type == 'uploadaudiofile') {
            $options = array(
                'upload_dir' => path('public') . 'files/temp/',
                'upload_url' => URL::base() . '/files/temp/',
                'param_name' => $element,
                'accept_file_types' => '/\.(mp3)$/i'
            );
        } else if ($type == 'uploadimage') {
            $options = array(
                'upload_dir' => path('public') . 'files/temp/',
                'upload_url' => URL::base() . '/files/temp/',
                'param_name' => $element,
                'accept_file_types' => '/\.(gif|jpe?g|png|tiff)$/i'
            );
        }
        $upload_handler = new UploadHandler($options);

        if (!Request::ajax()) {
            return;
        }

        $upload_handler->post("");
    }

    public function post_loadpage()
    {
        try {
            $currentUser = Auth::User();

            $contentFileID = (int)Input::get('contentfileid');
            $pageNo = (int)Input::get('pageno');

            $contentID = (int)ContentFile::find($contentFileID)->ContentID;
            $applicationID = (int)Content::find($contentID)->ApplicationID;
            $customerID = (int)Application::find($applicationID)->CustomerID;

            if (!Common::CheckContentOwnership($contentID)) {
                throw new Exception(__('error.unauthorized_user_attempt'));
            }

            if (!Common::AuthInteractivity($applicationID)) {
                throw new Exception(__('error.auth_interactivity'));
            }

            $ContentFilePageID = 0;

            $cfp = DB::table('ContentFilePage')
                ->where('ContentFileID', '=', $contentFileID)
                ->where('No', '=', $pageNo)
                ->where('StatusID', '=', eStatus::Active)
                ->first();
            if ($cfp) {
                $ContentFilePageID = (int)$cfp->ContentFilePageID;
            }

            $pageCount = DB::table('ContentFilePage')
                ->where('ContentFileID', '=', $contentFileID)
                ->where('StatusID', '=', eStatus::Active)
                ->count();

            $toolC = '';
            $propC = '';

            $pc = DB::table('PageComponent')
                ->where('ContentFilePageID', '=', $ContentFilePageID)
                ->where('StatusID', '=', eStatus::Active)
                ->get();
            foreach ($pc as $c) {
                $componentClass = PageComponent::find($c->PageComponentID)->Component()->Class;

                $cp = DB::table('PageComponentProperty')
                    ->where('PageComponentID', '=', $c->PageComponentID)
                    ->where('StatusID', '=', eStatus::Active)
                    ->get();

                $cpX = DB::table('PageComponentProperty')
                    ->where('PageComponentID', '=', $c->PageComponentID)
                    ->where('Name', '=', 'x')
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();

                $cpY = DB::table('PageComponentProperty')
                    ->where('PageComponentID', '=', $c->PageComponentID)
                    ->where('Name', '=', 'y')
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();

                $cpTriggerX = DB::table('PageComponentProperty')
                    ->where('PageComponentID', '=', $c->PageComponentID)
                    ->where('Name', '=', 'trigger-x')
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();

                $cpTriggerY = DB::table('PageComponentProperty')
                    ->where('PageComponentID', '=', $c->PageComponentID)
                    ->where('Name', '=', 'trigger-y')
                    ->where('StatusID', '=', eStatus::Active)
                    ->first();

                $x = 0;
                $y = 0;
                $trigger_x = 0;
                $trigger_y = 0;
                if ($cpX) {
                    $x = (int)$cpX->Value;
                }
                if ($cpY) {
                    $y = (int)$cpY->Value;
                }
                if ($cpTriggerX) {
                    $trigger_x = (int)$cpTriggerX->Value;
                }
                if ($cpTriggerY) {
                    $trigger_y = (int)$cpTriggerY->Value;
                }

                if ($componentClass == "audio" || $componentClass == "bookmark") {
                    $x = $trigger_x;
                    $y = $trigger_y;
                }

                $data = array(
                    'ComponentID' => $c->ComponentID,
                    'PageComponentID' => $c->PageComponentID,
                    'Process' => 'loaded',
                    'PageCount' => $pageCount,
                    'Properties' => $cp
                );
                $tool = View::make('interactivity.components.' . $componentClass . '.tool', $data)->render();
                //$tool = str_replace("{id}", $c->PageComponentID, $tool);
                $tool = str_replace("{id}", $c->No, $tool);
                $tool = str_replace("{name}", $componentClass, $tool);
                $tool = str_replace("{x}", $x, $tool);
                $tool = str_replace("{y}", $y, $tool);
                $tool = str_replace("{trigger-x}", $trigger_x, $tool);
                $tool = str_replace("{trigger-y}", $trigger_y, $tool);
                $toolC .= $tool;

                $prop = View::make('interactivity.components.' . $componentClass . '.property', $data)->render();
                //$prop = str_replace("{id}", $c->PageComponentID, $prop);
                $prop = str_replace("{id}", $c->No, $prop);
                $propC .= $prop;
            }
            return "success=" . base64_encode("true") . "&tool=" . base64_encode($toolC) . "&prop=" . base64_encode($propC);
        } catch (Exception $e) {
            return "success=" . base64_encode("false") . "&errmsg=" . base64_encode($e->getMessage());
        }
    }

}
