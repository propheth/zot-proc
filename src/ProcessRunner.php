<?php
namespace zot\proc;

interface ProcessRunner {
	public function run($cmd, $bg=false);
}

class ProcRunner implements ProcessRunner {

    public function run($cmd, $bg=false): ?string {
		$descriptors = array(
			//0 => array("pipe", "r"),  // STDIN
			1 => array("pipe", "w"),  // STDOUT
			2 => array("pipe", "w")   // STDERR
		);
		
		$proc = proc_open($cmd, $descriptors, $pipes);
		//fwrite($pipes[0], "Your data here...");
		//fclose($pipes[0]);
		
		/*
		stream_set_blocking($pipes[1], true);
		stream_set_blocking($pipes[2], true);
		*/
		$stdout = null;
		if(!$bg) {
			$stdout = stream_get_contents($pipes[1]);
			$stderr = stream_get_contents($pipes[2]);
		}
		
		fclose($pipes[1]);
		fclose($pipes[2]);
		
		$exitCode = proc_close($proc);

		return $stdout;
	}
	
	public static function available() {
		return !function_disabled('proc_open');
	}
}

class PopenRunner implements ProcessRunner {

    public function run($cmd, $bg=false): ?string {
		$process = popen($cmd, "r");

		$stdout = stream_get_contents($process);
		pclose($process);

		return $stdout;
	}
	
	public static function available() {
		return !function_disabled('popen');
	}
}

class HttpProcessRunner implements ProcessRunner {
	const PUBLIC_PROCESS_ENDPOINT = "http://localhost/commerceos/includes/libs/Process/public/HttpProcess.php";
	//const HTTP_PROCESS_ENDPOINT = "http://localhost/commerceos/includes/libs/Process/Process.php";
	const VERIFICATION_FILE_PREFIX = 'run';

    public function run($cmd=null, $bg=false) {
		$processVerificationPath = tempnam(sys_get_temp_dir(), self::VERIFICATION_FILE_PREFIX);
		$processCode = base64_encode($processVerificationPath);
		$processFile = base64_encode($cmd);

		$httpOpts = array(
			'http' => array(
				'header' => [
					"process-code: $processCode", 
					"process-file: $processFile", 
					"Connection: close"
				]
			)
		);

		$httpContext = stream_context_create($httpOpts);
		
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
		});

		$contents = null;
		try {
			$contents = file_get_contents(self::PUBLIC_PROCESS_ENDPOINT, false, $httpContext, 0, 0);
		}
		catch(\ErrorException $ex) {
			throw $ex;
		}
		finally {
			restore_error_handler();
			unlink($processVerificationPath);
		}

		return $contents;
	}
	
	public function isWorking() {
		try {
			(new HttpProcessRunner())->run();
			return true;
		}
		catch(\ErrorException $ex) { return false; }
	}

	public static function available() {
		return !function_disabled('file_get_contents');
	}
}

function random_chars($len) {
	$set = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$setLen = strlen($set);
	$randomStr = "";
	for($i = 0; $i < $len; $i++) {
		$randomStr .= $set[rand(0, $setLen - 1)]; }
	return $randomStr;
}

function function_disabled($func) {
	$disableFunctions = explode(',', ini_get('disable_functions'));
	return in_array($func, $disableFunctions);
}


?>