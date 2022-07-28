<?php
namespace zot\proc;

require_once(dirname(__FILE__).'/ProcessRunner.php');

class Process {
	const RUNNERS = [
		ProcRunner::class,
		PopenRunner::class
	];

	public static function runPhpFile($filePath) {
		try {
			$runner = self::getRunner();
			$cmdLine = self::buildCmdLinePhp($filePath);
			return $runner->run($cmdLine);
		}
		catch(\Exception $e1) {
			if(HttpProcessRunner::available()) {
				$runner = new HttpProcessRunner();
				return $runner->run($filePath);
			}
		}
	}

	public static function runPhpFileBg($filePath) {
		try {
			$runner = self::getRunner();
			$cmdLine = self::buildCmdLinePhp($filePath, true);
			return $runner->run($cmdLine);
		}
		catch(\Exception $e1) {
			if(HttpProcessRunner::available()) {
				$runner = new HttpProcessRunner();
				return $runner->run($filePath);
			}
		}
	}

	public static function runPhpCode($code, $delay=null) {
		if($delay !== null) { $code = "sleep($delay);".$code; }
		$escapedCode = str_replace('"', '\"', $code);
		$cmdLine = self::buildCmdLinePhp("-r \"$escapedCode\"");
		return self::getRunner()->run($cmdLine);
	}

	public static function runPhpCodeBg($code, $delay=null) {
		if($delay !== null) { $code = "sleep($delay);".$code; }
		$escapedCode = str_replace('"', '\"', $code);
		$cmdLine = self::buildCmdLinePhp("-r \"$escapedCode\"", true);
		return self::getRunner()->run($cmdLine, true);
	}

	public static function run($cmd) {
		$cmdLine = self::buildCmdLine($cmd);
		return self::getRunner()->run($cmdLine);
	}

	public static function runBg($cmd) {
		$cmdLine = self::buildCmdLine($cmd, true);
		return self::getRunner()->run($cmdLine);
	}

	private static function getRunner() {
		foreach(self::RUNNERS as $runner) {
			if($runner::available()) {
				return new $runner();
			}
		}
		throw new \Exception("No runners available.");
	}

	public static function getAvailableRunners() {
		$runners = array();
		foreach(self::RUNNERS as $runner) {
			if($runner::available()) {
				$runners[] = $runner;
			}
		}
		return $runners;
	}

	private static function buildCmdLine($cmd, $background=false) {
		$cmdLine = null;

		if(stripos(PHP_OS, 'WIN') !== false) {
			// windows
			$cmdLine = ($background===true?"start /min ":"")."$cmd";
		}
		else if(stripos(PHP_OS, 'LINUX') !== false) {
			// linux
			$cmdLine = "$cmd > /dev/null".($background===true?" &":"");
		}
		else if(stripos(PHP_OS, 'DARWIN') !== false) {
			// osx
			$cmdLine = "$cmd > /dev/null".($background===true?" &":"");
		}

		return $cmdLine;
	}

	private static function buildCmdLinePhp($args, $background=false) {
		$cmdLine = null;

		if(stripos(PHP_OS, 'WIN') !== false) {
			// windows
			$phpPath = null;
			if(stripos(basename(PHP_BINARY), 'php') === 0) {
				$phpPath = PHP_BINARY;
			}
			else if(is_executable(dirname(php_ini_loaded_file()).'/php.exe')) {
				$phpPath = dirname(php_ini_loaded_file()).'/php.exe';
			}
			else if(is_executable(getenv('PHPRC').'/php.exe')) {
				$phpPath = getenv('PHPRC').'/php.exe';	
			}
			
			$cmdLine = self::buildCmdLine("$phpPath $args", $background);
		}
		else if(stripos(PHP_OS, 'LINUX') !== false) {
			// linux
			$cmdLine = self::buildCmdLine(PHP_BINARY." $args");
		}
		else if(stripos(PHP_OS, 'DARWIN') !== false) {
			// osx
			$cmdLine = self::buildCmdLine(PHP_BINARY." $args");
		}
//var_dump($cmdLine);
		return $cmdLine;
	}
}

//var_dump(Process::runPhpFileBg("e:/propheth/repo/commerceos/includes/plugins/core/email/ProcessQueuedEmails.php"));
//var_dump(Process::runPhpCode("print file_get_contents(\"http://www.testingmcafeesites.com/testcat_ac.html\");"));

//var_dump((new HttpProcessRunner())->run("http://localhost/commerceos/includes/plugins/core/email/public/ProcessQueuedEmails.php"));
//require_once(__DIR__."/../../zot/init.php");
//var_dump(Process::runPhpFileBg(\zot\app_path("plugins/core/email/ProcessQueuedEmails.php")));
?>