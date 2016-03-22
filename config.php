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

// TODO: You must configure these fields for the starter project to function.
// Visit https://developers.google.com/glass/getting-started to learn more
$api_client_id = "";
$api_client_secret = "";
$api_simple_key = "";

define('FACEBOOK_ID', '');
define('FACEBOOK_SECRET', '');

$db_user = "";
$db_password = "";
$db_host = "localhost";


$base_url = "https://www.tesseractmobile.com/glass/glassface";
$image_url = "http://www.tesseractmobile.com/glass/glassface/images";
$app_name = "Glass To Facebook Beta";
$app_id = "glass-to-facebook-beta";
$static_url = "http://www.tesseractmobile.com/glass/glassface/static/";
$contact_card = $static_url . 'GlassFBcrd.png';
$image_contact = $static_url . 'Card-Mockup.png';
$image_facebook_connected = $static_url . 'Login-Check-Mockup.png';
$image_facebook = $static_url . 'Login-Mockup.png';
$link_url = "http://www.tesseractmobile.com/glass/glassface";

$sqlite_database = "/tmp/database.sqlite";

function getDataBase(){
	$datbase = mysqli_connect($db_host, $db_user, $db_password, 'glasspost') or die('Error');
	return $datbase;
}

function saveToken($token, $google_token){
	$dbc = getDataBase();
	$sql = "REPLACE INTO tokens (google_token, facebook_token) values(\"$google_token\", \"$token\")";
	mysqli_query($dbc, $sql);
}

function getToken($google_token){
	$dbc = getDataBase();
	$sql = "SELECT facebook_token from tokens where google_token = \"$google_token\"";
	$result = mysqli_query($dbc, $sql);
	if($row = mysqli_fetch_assoc($result)){	
		return $row['facebook_token'];
	}
	return FALSE;
}
