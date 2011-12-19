<?php

session_start();

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
$url = isset($_POST["url"]) ? $_POST["url"] : false;
$http_headers = isset($_POST["format"]) && $_POST["format"] == "JSON" ? array("x-li-format"=>"json") : array();
$http_method = OAUTH_HTTP_METHOD_GET;

include("defaults.php");


$oauth = new OAuth($_SESSION["apiKeys"]["consumer_key"], $_SESSION["apiKeys"]["consumer_secret"]);
$oauth->enableDebug();

switch($action) {
	case "logout": 
		session_destroy();
		
		printf("<html><head><script>window.onload = function() { window.opener.location.reload(); window.close(); };</script></head><body></body></html>");
		
		break;
		
	case "setKeys":
		if(isset($_POST["consumer_key"]) && isset($_POST["consumer_secret"])) {
			$_SESSION["apiKeys"] = array("consumer_key"=>$_POST["consumer_key"],
									  "consumer_secret"=>$_POST["consumer_secret"]);
			$_SESSION["my_key"] = true;
			$_SESSION["my_login"] = true;
			header("Location: request.php?action=requestToken");
			
		} else {
			print("<html><head><title>Use your own API keys</title></head><body><form action='' method='post'>
				Consumer Key: <input type='text' name='consumer_key'><br>
				Consumer Secret: <input type='text' name='consumer_secret'><br>
				<input type='submit' value='Submit'>
				</form></body></html>");
		}
		break;
		
	case "requestToken":
		$_SESSION["requestToken"] = $oauth->getRequestToken("https://api.linkedin.com/uas/oauth/requestToken", $callback_url . "?action=accessToken");
		
		// printf("<html><head><script>window.onload = function() { window.open('https://www.linkedin.com/uas/oauth/authenticate?oauth_token=%s'); };</script></head><body>%s</body></html>", $_SESSION["requestToken"]["oauth_token"], print_r($_SESSION["requestToken"]));
		
		header("Location: https://www.linkedin.com/uas/oauth/authenticate?oauth_token=" . $_SESSION["requestToken"]["oauth_token"]);
		
		break;
	case "accessToken":
		try {
			$oauth->setToken($_SESSION["requestToken"]["oauth_token"], $_SESSION["requestToken"]["oauth_token_secret"]);
			$_SESSION["accessToken"] = $oauth->getAccessToken("https://api.linkedin.com/uas/oauth/accessToken", "", $_REQUEST["oauth_verifier"]);
			$_SESSION["my_login"] = true;
		} catch(Exception $e) {
			print($oauth->getLastResponse());
		}
		
		printf("<html><head><script>window.onload = function() { window.opener.location.reload(); window.close(); };</script></head><body></body></html>");
		
		break;
		
	case "dumpSession":
		// print_r($_SESSION);
		// 	break;
		
	default: 
		if(!isset($_POST["csrf"]) || $_POST["csrf"] !== $_SESSION["csrf"]) {
			print('<pre style="color: white;">An error occurred. Please refresh the page to continue.</pre>');
			exit();
		}
	
		$oauth->setToken($_SESSION["accessToken"]["oauth_token"], $_SESSION["accessToken"]["oauth_token_secret"]);

		$response = new stdClass();
		
		try {
			$oauth->fetch($url, array(), $http_method, $http_headers);
		} catch(Exception $e) {
			$response->exception = str_replace(__FILE__, basename(__FILE__), wordwrap(htmlentities(print_r($e, true)), 133, "\n", true));			
			
			print("<h1 style='color: red;'>Error</h1>");
		}

		$response->response = htmlentities($oauth->getLastResponse());
		$response->info = htmlentities(print_r($oauth->getLastResponseInfo(), true));
		$response->headers = htmlentities(print_r($oauth->getLastResponseHeaders(), true));
		$response->debug = wordwrap(htmlentities(print_r($oauth->debugInfo, true)), 133, "\n", true);

		printf("<script>resview = %s;</script>", json_encode($response));
		
?>
<div>
	<button onclick="javascript: document.getElementById('display').innerHTML = resview.response;">Response Body</button>
	<button onclick="javascript: document.getElementById('display').innerHTML = resview.info;">Response Info</button>
	<button onclick="javascript: document.getElementById('display').innerHTML = resview.headers;">Response Headers</button>
	<button onclick="javascript: document.getElementById('display').innerHTML = resview.debug;">Debug</button>
	<?php if(isset($response->exception)): ?><button onclick="javascript: document.getElementById('display').innerHTML = resview.exception;">Exception</button><?php endif; ?>
</div><hr><br>
<?php
		
		printf('<pre style="color: white;" id="display">%s</pre>', htmlentities($oauth->getLastResponse()));
		
		$_SESSION["csrf"] = base_convert(rand(1E10,1E20), 10, 32);
		
		printf('<script>window.parent.csrf="%s"</script>', $_SESSION["csrf"]);
		
		break;
}




