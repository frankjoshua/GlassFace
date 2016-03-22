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

$client = get_google_api_client();

// Authenticate if we're not already
if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid']) == null) {
  header('Location: ' . $base_url . '/oauth2callback.php');
  exit;
} else {
  $client->setAccessToken(get_credentials($_SESSION['userid']));
}

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

// But first, handle POST data from the form (if there is any)
if(isset($_POST['operation'])){
	switch ($_POST['operation']) {
	  
	  case "insertSubscription":
	    $message = subscribeToNotifications($mirror_service, $_POST['subscriptionId'],
	      $_SESSION['userid'], $base_url . "/notify.php");
	    break;
	  case "deleteSubscription":
	    $message = $mirror_service->subscriptions->delete($_POST['subscriptionId']);
	    break;
	  case "insertContact":
	    insertContact($mirror_service, $app_id, $app_name,
	        $contact_card);
	    $message = "Contact inserted. Enable it on MyGlass.";
	    break;
	  case "deleteContact":
	    deleteContact($mirror_service, $app_id);
	    $message = "Contact deleted.";
	    die();
	}
}

try {
  $contact = $mirror_service->contacts->get($app_name);
} catch (Exception $e) {
  //If no contact found add one
  $contact = null;

	insertContact($mirror_service, $app_id, $app_name, $contact_card);
	
}

$facebook = new Facebook(array(
  'appId'  => FACEBOOK_ID,
  'secret' => FACEBOOK_SECRET,
));

// Get User ID
$user = $facebook->getUser();

if ($user) {
	
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
	
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
	
  }
} 
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
	//Don't allow logout yet
  $connect_url = "#";
  $facebook_image = $image_facebook_connected;
	
	$token = $facebook->getAccessToken();
	
	saveToken($token, $_SESSION['userid']);
  
 } else {
  $loginUrl = $facebook->getLoginUrl(array('scope' =>'publish_stream','redirect_uri'=> $base_url . '/index.php'));
  $connect_url = $loginUrl;
  $facebook_image = $image_facebook;
}

?>
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $app_name; ?></title>
  <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  <style>
    .button-icon { max-width: 75px; }
    .tile {
      border-left: 1px solid #444;
      padding: 5px;
      list-style: none;
    }
    .btn { width: 100%; }
  </style>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#"><?php echo $app_name; ?></a>
      <div class="nav-collapse collapse">
        <form class="navbar-form pull-right" action="signout.php" method="post">
          <button type="submit" class="btn">Sign out</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="container">
	<br><br><br>
	<h2>1. Connect to Facebook</h2>
	<a href="<?php echo $connect_url; ?>"><img src="<?php echo $facebook_image; ?>" /></a>
</div>
<div class="container">
	<br><br><br>
	<h2>2. Please enable <?php echo $app_name; ?> on <a href="https://glass.google.com/myglass/share" target="_blank">MyGlass > Sharing Contacts (Near Bottom)</h2>
	<img src="<?php echo $image_contact; ?>"/></a>
</div>
<div class="container">
	<br><br><br>
	<h2>3. Take a picture with Glass.</h2>
</div>
<div class="container">
	<h2>4. Share you picture with Glass To Facebook</h2>
</div>
<div class="container">
	<h2>5. Your picture will appear in your Facebook page.</h2>
</div>
<!--
<form class="span3" method="post">
        <input type="hidden" name="operation" value="deleteContact">
        <input type="hidden" name="id" value="<?php echo $app_name; ?>">
        <button class="btn" type="submit">Delete <?php echo $app_name; ?></button>
      </form>
-->   
<script
    src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="./static/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
