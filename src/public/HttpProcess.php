<?php

function get_current_headers() {
	$headers = array();
	foreach($_SERVER as $key => $value) {
		if(substr($key, 0, 5) === "HTTP_") {
			$name = strtolower(str_replace(array('_', ' '), '-', substr($key, 5)));
			$headers[$name] = $value;
		}
	}
	return $headers;
}

$authorized = true;

if($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR']) {
	$authorized = false;
}

$headers = get_current_headers();

$processCode = $headers['process-code'];
$processVerificationPath = base64_decode($processCode);

if(!file_exists($processVerificationPath)) {
	$authorized = false;
}

$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

if($authorized) {
	header("$protocol 200 OK");
	header("Content-Length: 0");
	header('Content-Encoding: none');
	header("Connection: close");
	//while(ob_get_level() > 0) { ob_end_flush(); } // flush out all buffers
	flush();
}
else {		
	header("$protocol 401 Unauthorized"); 
	die;
}

if(array_key_exists('process-file', $headers)) {
	$processFile = $headers['process-file'];
	$processFilePath = base64_decode($processFile);
	if(!empty($processFilePath) && is_readable($processFilePath)) {
		require($processFilePath); 
	}
}

?>