<?php

class Webservice_Applications_Controller extends Base_Controller
{

    public static $availableServices = array(103);
    public $restful = true;

    /**
     * *****THIS FUNCTION IS DEPRECATED DONT USE THIS***
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function get_version($ServiceVersion, $applicationID)
    {

        return webService::render(function () use ($ServiceVersion, $applicationID) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);
            return Response::json(array(
                'status' => 0,
                'error' => "",
                'ApplicationID' => (int)$application->ApplicationID,
                'ApplicationBlocked' => ((int)$application->Blocked == 1 ? true : false),
                'ApplicationStatus' => ((int)$application->Status == 1 ? true : false),
                'ApplicationVersion' => (int)$application->Version
            ));
        });
    }

    public static function checkServiceVersion($ServiceVersion)
    {
        if (!in_array($ServiceVersion, self::$availableServices)) {
            throw eServiceError::getException(eServiceError::ServiceNotFound);
        }
    }

    /**
     *
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function post_version($ServiceVersion, $applicationID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);
            $accessToken = Input::get('accessToken', "");
            $clientVersion = 0;
            if (!empty($accessToken)) {
                $client = webService::getClientFromAccessToken($accessToken, $application->ApplicationID);
                $clientVersion = $client->Version;
            }

            //Bu responsedaki Application Version Application tablosundaki Versiyon degildir.
            //Client icin ozel olusturulan Application Versiondur.
            return Response::json(array(
                'status' => 0,
                'error' => "",
                'ApplicationID' => (int)$application->ApplicationID,
                'ApplicationBlocked' => ((int)$application->Blocked == 1 ? true : false),
                'ApplicationStatus' => ((int)$application->Status == 1 ? true : false),
                'ApplicationVersion' => (int)($application->Version + $clientVersion)
            ));
        });
    }

    /**
     * Control for force update of application
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function get_detail($ServiceVersion, $applicationID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            /*
              INFO: Force | guncellemeye zorlanip zorlanmayacagini selimin tablosundan sorgula!
              0: Zorlama
              1: Uyari goster
              2: Zorla
              3: Sil ve zorla
             */
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            /* @var $application Application */
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);
            $customer = webService::getCheckCustomer($ServiceVersion, $application->CustomerID);

            //INFO:Save token method come from get_contents
            webService::saveToken($ServiceVersion, $customer->CustomerID, $applicationID);

            return Response::json(array(
                'status' => 0,
                'error' => "",
                'CustomerID' => (int)$customer->CustomerID,
                'CustomerName' => $customer->CustomerName,
                'ApplicationID' => (int)$application->ApplicationID,
                'ApplicationName' => $application->Name,
                'ApplicationDetail' => $application->Detail,
                'ApplicationExpirationDate' => $application->ExpirationDate,
                'IOSVersion' => $application->IOSVersion,
                'IOSLink' => $application->IOSLink,
                'AndroidVersion' => $application->AndroidVersion,
                'AndroidLink' => $application->AndroidLink,
                'PackageID' => $application->PackageID,
                'ApplicationBlocked' => ((int)$application->Blocked == 1 ? true : false),
                'ApplicationStatus' => ((int)$application->Status == 1 ? true : false),
                'ApplicationVersion' => (int)$application->Version,
                'Force' => (int)$application->Force,
                'SubscriptionWeekActive' => (int)$application->SubscriptionWeekActive,
                'SubscriptionWeekIdentifier' => $application->SubscriptionIdentifier(Subscription::week),
                'SubscriptionMonthActive' => (int)$application->SubscriptionMonthActive,
                'SubscriptionMonthIdentifier' => $application->SubscriptionIdentifier(Subscription::mounth),
                'SubscriptionYearActive' => (int)$application->SubscriptionYearActive,
                'SubscriptionYearIdentifier' => $application->SubscriptionIdentifier(Subscription::year),
                'WeekPrice' => '',
                'MonthPrice' => '',
                'YearPrice' => '',
            ));
        });
    }

    /**
     * Control for force update of application
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function post_detail($ServiceVersion, $applicationID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            /*
              INFO: Force | guncellemeye zorlanip zorlanmayacagini selimin tablosundan sorgula!
              0: Zorlama
              1: Uyari goster
              2: Zorla
              3: Sil ve zorla
             */
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            /* @var $application Application */
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);
            $customer = webService::getCheckCustomer($ServiceVersion, $application->CustomerID);

//INFO:Save token method come from get_contents
            webService::saveToken($ServiceVersion, $customer->CustomerID, $applicationID);

            return Response::json(array(
                'status' => 0,
                'error' => "",
                'CustomerID' => (int)$customer->CustomerID,
                'CustomerName' => $customer->CustomerName,
                'ApplicationID' => (int)$application->ApplicationID,
                'ApplicationName' => $application->Name,
                'ApplicationDetail' => $application->Detail,
                'ApplicationExpirationDate' => $application->ExpirationDate,
                'IOSVersion' => $application->IOSVersion,
                'IOSLink' => $application->IOSLink,
                'AndroidVersion' => $application->AndroidVersion,
                'AndroidLink' => $application->AndroidLink,
                'PackageID' => $application->PackageID,
                'ApplicationBlocked' => ((int)$application->Blocked == 1 ? true : false),
                'ApplicationStatus' => ((int)$application->Status == 1 ? true : false),
                'ApplicationVersion' => (int)$application->Version,
                'Force' => (int)$application->Force,
                'SubscriptionWeekActive' => (int)$application->SubscriptionWeekActive,
                'SubscriptionWeekIdentifier' => $application->SubscriptionIdentifier(Subscription::week),
                'SubscriptionMonthActive' => (int)$application->SubscriptionMonthActive,
                'SubscriptionMonthIdentifier' => $application->SubscriptionIdentifier(Subscription::mounth),
                'SubscriptionYearActive' => (int)$application->SubscriptionYearActive,
                'SubscriptionYearIdentifier' => $application->SubscriptionIdentifier(Subscription::year),
                'WeekPrice' => '',
                'MonthPrice' => '',
                'YearPrice' => '',
            ));
        });
    }

    /**
     *
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function get_categories($ServiceVersion, $applicationID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            webService::getCheckApplication($ServiceVersion, $applicationID);
            $categories = webService::getCheckApplicationCategories($ServiceVersion, $applicationID);
            return Response::json(array(
                'status' => 0,
                'error' => "",
                'Categories' => $categories
            ));
        });
    }

    /**
     *
     * @param int $ServiceVersion
     * @param int $applicationID
     * @param int $categoryID
     * @return Laravel\Response
     */
    public function get_categoryDetail($ServiceVersion, $applicationID, $categoryID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID, $categoryID) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            webService::getCheckApplication($ServiceVersion, $applicationID);
            $category = webService::getCheckApplicationCategoryDetail($ServiceVersion, $applicationID, $categoryID);
            return Response::json(array(
                'status' => 0,
                'error' => "",
                'CategoryID' => (int)$category->CategoryID,
                'CategoryName' => $category->Name
            ));
        });
    }

    /**
     *
     * @param int $ServiceVersion
     * @param int $applicationID
     * @return Laravel\Response
     */
    public function get_contents($ServiceVersion, $applicationID)
    {
        //get user token here then return acourdin to this 571571
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            $isTest = Input::get('isTest', 0) ? TRUE : FALSE;
            $accessToken = Input::get('accessToken', "");
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);
            $baseUrl = Config::get('custom.url');
            $tabs = array();
            $tabs[] = array("tabLogoUrl" => $baseUrl . "img/galeLogo.png", "tabUrl" => $baseUrl . "/maps/webview/" . $application->ApplicationID);
            $tabs[] = array("tabLogoUrl" => $baseUrl . "img/bg-drop.png", "tabUrl" => "http://www.google.com/");

            $serviceData = array();
            $serviceData["ServiceVersion"] = $ServiceVersion;
            $serviceData["application"] = $application;
            $serviceData["isTest"] = $isTest;
            $serviceData["accessToken"] = $accessToken;
            $contents = webService::getCheckApplicationContents($serviceData);
            $status = 0;
            $error = "";

            if (empty($contents)) {
                $status = eServiceError::ContentNotFound;
                $error = eServiceError::ContentNotFoundMessage;
            }

            $activeSubscription = false;
            $subscriptionEndDate = date("Y-m-d H:i:s");
            $remainingDay = 0;
            if (!empty($accessToken)) {
                $client = webService::getClientFromAccessToken($accessToken, $application->ApplicationID);
                if ($client->PaidUntil > date("Y-m-d H:i:s", strtotime("-1 day"))) {
                    //bir gun fazladan verdim...
                    $activeSubscription = true;
                    $subscriptionEndDate = $client->PaidUntil;
                    $remainingDay = ceil((strtotime($client->PaidUntil) - time()) / 86400);
                }
            }

            return Response::json(array(
                'status' => $status,
                'error' => $error,
                'ActiveSubscription' => $activeSubscription,
                'RemainingDay' => $remainingDay,
                'SubscriptionEndDate' => $subscriptionEndDate,
                'ThemeBackground' => $application->ThemeBackground,
                'ThemeForeground' => $application->ThemeForegroundColor,
                'BannerActive' => $application->BannerActive,
                'BannerPage' => $application->BannerPage(),
                'Tabs' => $application->TabsForService(),
                'Contents' => $contents
            ));
        });
    }

    /**
     * This function is used for Gp Viewer Application
     * Other Applications dont use this function
     * @param int $ServiceVersion
     * @return type
     */
    public function post_authorized_application_list($ServiceVersion)
    {
        return webService::render(function () use ($ServiceVersion) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            $username = Input::get('username');
            $password = Input::get('password');
            $applicationSet = array();
            $userFacebookID = Input::get('userFacebookID');
            $userFbEmail = Input::get('userFbEmail');
            $responseSet = array();
            $user = webService::getCheckUser($ServiceVersion, $username, $password, $userFacebookID, $userFbEmail);

            //We have a user now...
            if ($user->UserTypeID == eUserTypes::Customer) {
                $applicationSet = Application::where('CustomerID', '=', $user->CustomerID)
                    ->where('ExpirationDate', '>=', DB::raw('CURDATE()'))
                    ->where('StatusID', '=', eStatus::Active)
                    ->get();
            } else if ($user->UserTypeID == eUserTypes::Manager) {
                //admin
                $applicationSet = Application::where('ExpirationDate', '>=', DB::raw('CURDATE()'))
                    ->where('StatusID', '=', eStatus::Active)
                    ->get();
            }

            foreach ($applicationSet as $application) {
                $responseSet[] = array(
                    'ApplicationID' => $application->ApplicationID,
                    'Name' => $application->Name
                );
            }

            return Response::json($responseSet);
        });
    }

    /**
     * Authenticate the client to the application
     * @param type $ServiceVersion
     * @return Laravel\Response
     */
    public function post_login_application($ServiceVersion)
    {
        return webService::render(function () use ($ServiceVersion) {
            $applicationID = Laravel\Input::get("applicationID");
            $username = Laravel\Input::get("username");
            $password = Laravel\Input::get("password");

            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            webService::getCheckApplication($ServiceVersion, $applicationID);
            $client = webService::getCheckClient($ServiceVersion, $applicationID, $username, $password);

            if (!$client) {
                return FALSE;
            }

            $result = array();
            $result['accessToken'] = $client->Token;
            return Laravel\Response::json($result);
        }
        );
    }

    public function post_fblogin($ServiceVersion)
    {
        return webService::render(function () use ($ServiceVersion) {
            /* @var $client Client */
            $rules = array(
                'clientLanguage' => 'required|in:' . implode(",", Laravel\Config::get("application.languages")),
                'applicationID' => 'required|integer',
                'facebookToken' => 'required',
                'facebookUserId' => 'required',
                'facebookEmail' => 'required|email',
                'name' => 'required',
                'surname' => 'required',
            );


            $v = Laravel\Validator::make(\Laravel\Input::all(), $rules);
            if ($v->invalid()) {
                throw eServiceError::getException(eServiceError::GenericError, $v->errors->first());
            }

            $applicationID = Laravel\Input::get("applicationID");
            $facebookToken = Laravel\Input::get("facebookToken");
            $facebookUserId = Laravel\Input::get("facebookUserId");
            $facebookEmail = Laravel\Input::get("facebookEmail");
            $name = Laravel\Input::get("name");
            Laravel\Config::set("application.language", Laravel\Input::get("clientLanguage"));
            $surname = Laravel\Input::get("surname");
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            webService::getCheckApplication($ServiceVersion, $applicationID);
            $client = Client::where("ApplicationID", "=", $applicationID)
                ->where("StatusID", "=", eStatus::Active)
                ->where("Email", "=", $facebookEmail)->first();
            if ($client) {
                $result = array();
                $result['accessToken'] = $client->Token;
                return Laravel\Response::json($result);
            }

            $flag = TRUE;
            $userNo = "";
            do {
                $username = Laravel\Str::ascii($name . $surname . $userNo);
                $clientExists = Client::where("Username", "=", $username)
                    ->where("ApplicationID", "=", $applicationID)
                    ->first();

                if (!$clientExists) {

                    //register client
                    $flag = FALSE;
                    $password = Common::generatePassword();
                    $clientNew = new Client();
                    $clientNew->Password = $password;
                    $clientNew->Token = $username . "_" . md5(uniqid());
                    $clientNew->StatusID = eStatus::Active;
                    $clientNew->CreatorUserID = 0;
                    $clientNew->Username = $username;
                    $clientNew->Email = $facebookEmail;
                    $clientNew->ApplicationID = $applicationID;
                    $clientNew->Name = $name;
                    $clientNew->Surname = $surname;
                    $clientNew->save();
                    $application = Application::find($applicationID);

                    //send mail to client
                    $subject = __('clients.registered_email_subject', array('Application' => $application->Name));
                    $msg = __('clients.registered_email_message', array(
                            'Application' => $application->Name,
                            'firstname' => $clientNew->Name,
                            'lastname' => $clientNew->Surname,
                            'username' => $clientNew->Username,
                            'pass' => $password
                        )
                    );

                    Common::sendEmail($clientNew->Email, $clientNew->Name . ' ' . $clientNew->Surname, $subject, $msg);
                } else {
                    $userNo = 1 + (int)$userNo;
                }
            } while ($flag);

            $result = array();
            $result['accessToken'] = $clientNew->Token;
            return Laravel\Response::json($result);
        });
    }

    public function post_receipt($ServiceVersion, $applicationID)
    {
        return webService::render(function () use ($ServiceVersion, $applicationID) {
            Webservice_Applications_Controller::checkServiceVersion($ServiceVersion);
            $application = webService::getCheckApplication($ServiceVersion, $applicationID);

            $rules = array(
                'accessToken' => 'required',
                'purchaseToken' => 'required',
                'packageName' => 'required',
                'productId' => 'required',
                'platformType' => 'required|in:android,ios',
            );

            $v = Laravel\Validator::make(\Laravel\Input::all(), $rules);
            if ($v->invalid()) {
                throw eServiceError::getException(eServiceError::GenericError, $v->errors->first());
            }

            $accessToken = \Laravel\Input::get('accessToken');
            $purchaseToken = \Laravel\Input::get('purchaseToken'); //receiptToken
            $packageName = \Laravel\Input::get('packageName');
            $productID = \Laravel\Input::get('productId'); //subscriptionIdentifier
            $platformType = \Laravel\Input::get('platformType');
            $myClient = webService::getClientFromAccessToken($accessToken, $applicationID);

            //ise baslamadan gonderilen receipti kaydedelim...
            /** @var ClientReceipt $clientReceipt */
            $clientReceipt = ClientReceipt::where("Receipt", "=", $purchaseToken)->first();
            if (!$clientReceipt) {
                $clientReceipt = new ClientReceipt();
            } else {
                if ($clientReceipt->SubscriptionID != $productID || $clientReceipt->ClientID != $myClient->ClientID) {
                    throw eServiceError::getException(eServiceError::GenericError, 'Receipt used for another product or client');
                }
            }
            $clientReceipt->ClientID = $myClient->ClientID;
            $clientReceipt->SubscriptionID = $productID;
            $clientReceipt->PackageName = $packageName;
            $clientReceipt->Receipt = $purchaseToken;
            $clientReceipt->save();

            switch ($platformType) {
                case 'android':
                    require_once path('bundle') . '/google/src/Google/autoload.php';
                    $client = new Google_Client();
                    // set Application Name to the name of the mobile app
                    $client->setApplicationName("Uni Saç");
                    // get p12 key file
                    //echo path('public') . 'keys/GooglePlayAndroidDeveloper-74176ee02cd0.p12'; exit;
                    $key = file_get_contents(path('app') . 'keys/GooglePlayAndroidDeveloper-74176ee02cd0.p12');

                    // create assertion credentials class and pass in:
                    // - service account email address
                    // - query scope as an array (which APIs the call will access)
                    // - the contents of the key file
                    $cred = new Google_Auth_AssertionCredentials(
                        '552236962262-compute@developer.gserviceaccount.com',
                        array('https://www.googleapis.com/auth/androidpublisher'),
                        $key
                    );
                    // add the credentials to the client
                    $client->setAssertionCredentials($cred);
                    // create a new Android Publisher service class
                    $service = new Google_Service_AndroidPublisher($client);
                    // use the purchase token to make a call to Google to get the subscription info
                    /** @var Content $content */
                    $content = Content::where("Identifier", '=', $productID)->where("ApplicationID", '=', $applicationID)->first();
                    if ($content) {
                        //content ise valide edip contenti erişebilir content listesine koyacağız...
                        $productPurchaseResponse = $service->purchases_products->get($packageName, $productID, $purchaseToken);
                        $clientReceipt->SubscriptionType = $productPurchaseResponse->getKind();
                        $clientReceipt->MarketResponse = json_encode($productPurchaseResponse->toSimpleObject());
                        $clientReceipt->save();

                        if ($productPurchaseResponse->consumptionState == webService::GoogleConsumptionStatePurchased) {
                            //Content bought so save content to clients purchased products
                            $myClient->addPurchasedItem($content->ContentID);
                        } else {
                            throw eServiceError::getException(eServiceError::GenericError, 'Content Not Bought.');
                        }

                    } else {
                        //applicationda $productID var mi kontrol edecegiz...
                        $subscription = $service->purchases_subscriptions->get($packageName, $productID, $purchaseToken);
                        $clientReceipt->MarketResponse = json_encode($subscription->toSimpleObject());
                        if (is_null($subscription) || !$subscription->getExpiryTimeMillis() > 0) {
                            $clientReceipt->save();
                            throw eServiceError::getException(eServiceError::GenericError, 'Error validating transaction.');
                        }

                        //validate oldu tekrar kaydedelim...
                        $clientReceipt->SubscriptionType = $subscription->getKind();
                        $clientReceipt->SubscriptionStartDate = date("Y-m-d H:i:s", $subscription->getStartTimeMillis() / 1000);
                        $clientReceipt->SubscriptionEndDate = date("Y-m-d H:i:s", $subscription->getExpiryTimeMillis() / 1000);
                        $clientReceipt->save();


                        if (empty($myClient->PaidUntil) || $myClient->PaidUntil < $clientReceipt->SubscriptionEndDate) {
                            $myClient->PaidUntil = $clientReceipt->SubscriptionEndDate;
                            $myClient->save();
                        }
                    }
                    break;
                case 'ios':
                    //validate olursa $clientReceipt'i ona gore kaydedicegiz...
                    $appleReceiptValidationUrl = 'https://sandbox.itunes.apple.com/verifyReceipt'; //571571
                    //$appleReceiptValidationUrl = 'https://buy.itunes.apple.com/verifyReceipt';

                    $receiptObject = webService::buildAppleJSONReceiptObject($purchaseToken, $application->IOSHexPasswordForSubscription);
                    $response = webService::makeAppleReceiptRequest($appleReceiptValidationUrl, $receiptObject);
                    $clientReceipt->MarketResponse = json_encode($response);
                    if (!isset($response["receipt"])) {
                        $clientReceipt->save();
                        throw eServiceError::getException(eServiceError::GenericError, 'Receipt not set.');
                    } elseif (!isset($response["receipt"]["in_app"])) {
                        $clientReceipt->save();
                        throw eServiceError::getException(eServiceError::GenericError, 'In-app not set.');
                    } elseif (!isset($response["status"])) {
                        $clientReceipt->save();
                        throw eServiceError::getException(eServiceError::GenericError, 'Response status not set');
                    } elseif ($response["status"] != 0) {
                        $clientReceipt->save();
                        throw eServiceError::getException(eServiceError::GenericError, 'Provided Receipt not valid.');
                    }

                    //apple icin butun receiptleri donup direk restore edicem...
                    foreach ($response["receipt"]["in_app"] as $key => $inApp) {
                        if (!isset($inApp['expires_date_ms'])) {
                            //expires_date_ms set edilmemis ise product satin almadir.
                            if (isset($inApp["product_id"])) {
                                $content = Content::where("Identifier", '=', $productID)->where("ApplicationID", '=', $applicationID)->first();
                                if (isset($content)) {
                                    $myClient->addPurchasedItem($content->ContentID, false);
                                }
                            }
                        } else {
                            //expires_date_ms set edilmis subscription satin alinmis.
                            $clientReceipt->SubscriptionType = "iospublisher#subscriptionPurchase";
                            $inAppExpiresDate = date("Y-m-d H:i:s", $inApp["expires_date_ms"] / 1000);
                            if (empty($myClient->PaidUntil) || $myClient->PaidUntil < $inAppExpiresDate) {
                                $myClient->PaidUntil = $inAppExpiresDate;
                            }

                            if ($key == count($response["receipt"]["in_app"]) - 1) {
                                //en son alinmis receipti kaydedelim...
                                $clientReceipt->SubscriptionStartDate = date("Y-m-d H:i:s", $inApp["purchase_date_ms"] / 1000);
                                $clientReceipt->SubscriptionEndDate = $inAppExpiresDate;
                                $clientReceipt->save();
                            }
                        }
                    }
                    $myClient->save();
                    break;
            }
            return Response::json(array('status' => 0, 'error' => "",));
        });
    }

}
