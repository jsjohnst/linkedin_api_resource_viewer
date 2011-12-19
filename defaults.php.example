<?php

$callback_url = "PUT IN YOUR CALLBACK URL HERE"; // should be PHP_SELF location

// hard coded access token for the default user
if(!isset($_SESSION["accessToken"])) {
	$_SESSION["accessToken"] = array("oauth_token"=>"ACCESS_TOKEN",
									 "oauth_token_secret"=>"ACCESS_TOKEN_SECRET");
}

// hard coded api keys for the default settings
if(!isset($_SESSION["apiKeys"])) {
	$_SESSION["apiKeys"] = array("consumer_key"=>"API_KEY",
							  	 "consumer_secret"=>"API_SECRET");
}