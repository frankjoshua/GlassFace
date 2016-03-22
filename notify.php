<?php
/*
 * Copyright (C) 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
//  Author: Jenny Murphy - http://google.com/+JennyMurphy
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
require 'facebook-php-sdk/src/facebook.php';

if($_SERVER['REQUEST_METHOD'] != "POST") {
  http_send_status(400);
  exit();
}

// Parse the request body
$body = http_get_request_body();
$request = json_decode($body, true);


// A notification has come in. If there's an attached photo, bounce it back
// to the user
$user_id = $request['userToken'];
$access_token = get_credentials($user_id);

$client = get_google_api_client();
$client->setAccessToken($access_token);

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

//Save image to file
$itemId = $request['itemId'];
$timeLineItem = $mirror_service->timeline->get($itemId);
$request = new Google_HttpRequest($timeLineItem['attachments'][0]['contentUrl'], 'GET', null, null);
$httpRequest = Google_Client::$io->authenticatedRequest($request);
if ($httpRequest->getResponseHttpCode() == 200) {
	//$image = $httpRequest->getResponseBody();
    //imagejpeg($image, 'test.jpg');
    file_put_contents('./images/' . $itemId . '.jpg', $httpRequest->getResponseBody());
  } else {
    // An error occurred.
    //die('This sucks! '. $httpRequest->getResponseBody());
  }

//Post to facebook
//sleep(5);
$facebook = new Facebook(array(
  'appId'  => FACEBOOK_ID,
  'secret' => FACEBOOK_SECRET,
));

$token = getToken($user_id);

//Upload Photo
$attachment = array(
	'access_token' => $token,
    'message' => 'Posted through Glass.',
    
    //'name' => 'This is my demo Facebook application!',
    //'caption' => "Caption of the Post",
    //'link' => $link_url,
    //'description' => $image_url . '/' . $itemId . '.jpg',
    'url' => $image_url . '/' . $itemId . '.jpg',
     //'picture' => 'http://tesseractmobile.biz/wp-content/themes/tesseract/images/googleplay.png',
    // 'actions' => array(
        // array(
            // 'name' => 'Tesseract Mobile',
            // 'link' => 'http://www.tesseractmobile.com',
        // )
    // )
);


$result = $facebook->api('/me/photos', 'post', $attachment);

//$pic = $facebook->api("/" . $result['id'], 'get', array('access_token' => $token));
//$pic_url = $pic->source;
//Create post with photo
$attachment = array(
	'access_token' => $token,
    'message' => 'Posted through Glass.',
    'object_attachment' => $result['id'],
    'name' => $app_name,
    'caption' => " ",
    //'link' => $link_url,
    //'description' => $image_url . '/' . $itemId . '.jpg',
   //'picture' => $image_url . '/' . $itemId . '.jpg',
   //'image' => $image_url . '/' . $itemId . '.jpg',
    'actions' => array(
        array(
            'name' => 'Tesseract Mobile',
            'link' => 'http://www.tesseractmobile.com',
        )
    )
);


$result = $facebook->api('/me/feed', 'post', $attachment);

//Send notification
//$timeline_item = new Google_TimelineItem();
//$timeline_item->setText("Got a notification: " . $pic_url);
// 
//insertTimelineItem($mirror_service, $timeline_item, null, null);




