<?php
	session_start();
	$_SESSION["csrf"] = base_convert(rand(1E10,1E20), 10, 32);

	$api_url = isset($_REQUEST["api_url"]) ? urldecode($_REQUEST["api_url"]) : "";

	if(parse_url($api_url, PHP_URL_HOST) !== "api.linkedin.com") {
		$api_url = "http://api.linkedin.com/v1/people/~";
	}
?>
<html>
<head>
	<title>API Response Viewer</title>
	<style>
		body {
			margin: 0;
			padding: 0;
			font-size: 20px;
		}
		.actionbar {
			padding: 0.5em;
			background-color: #33e;
		}
		.advanced {
			float: right;
		}
		.results {
			width: 100%;
			height: 100%;
			position: fixed;
			top: 45px;
			background-color: #333;
		}
		.results div {
			position: absolute;
			top: 15px;
			bottom: 15px;
			left: 15px;
			right: 15px;

		}
		#response {
			width: 100%;
			height: 100%;
			border: 0;
		}
		form {
			display: inline;
		}
	</style>
	<script>
		window.csrf = '<?php echo $_SESSION["csrf"]; ?>';
	</script>
</head>
<body>
	<div class="actionbar"><form action="request.php" method="post" target="response" onSubmit="document.getElementById('csrf').value=window.csrf; return true;">
		<input type="hidden" name="csrf" id="csrf"/>
		<select name="format"><option value="XML">XML</option><option value="JSON">JSON</option></select>
		<input type="text" name="url" size="75" value="<?php echo $api_url; ?>"/>
		<input type="submit" value="Show API result" /></form>
		<span class="advanced">
			<button id="auth"><?php if(isset($_SESSION["my_login"])): ?>Logout<?php else: ?>Login as Me<?php endif; ?></button>
			<button id="key"><?php if(isset($_SESSION["my_key"])): ?>Use default API Key<?php else: ?>Use my own API Key<?php endif; ?></button>
		</span>
	</div>
	<div class="results"><div>
		<iframe id="response" name="response"></iframe>
	</div></div>
</body>
<script>
	var auth_button = document.getElementById("auth");
	auth_button.addEventListener("click", function(e) {
		e.preventDefault();
		if(this.innerHTML == "Logout") {
			window.open("request.php?action=logout");
			this.innerHTML = "Login as Me";
		} else {
			window.open("request.php?action=requestToken");
			this.innerHTML = "Logout";
		}
	}, true);
	
	var key_button = document.getElementById("key");
	key_button.addEventListener("click", function(e) {
		e.preventDefault();
		if(this.innerHTML == "Use default API Key") {
			window.open("request.php?action=logout");
			this.innerHTML = "Use my own API Key";
			auth_button.innerHTML = "Login as Me";
		} else {
			window.open("request.php?action=setKeys");
			this.innerHTML = "Use default API Key";
		}
	}, true);
</script>
</html>
