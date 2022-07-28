
<?php
require_once(__DIR__.'/Test.php');
require_once(__DIR__.'/../src/Process.php');
require_once(__DIR__.'/../src/ProcessRunner.php');

use zot\test\Test;
use zot\proc\Process;

class Tests extends Test {

	public function testRunPhpCode() {
		try {
			Process::runPhpCode("print file_get_contents(\"http://www.testingmcafeesites.com/testcat_ac.html\");");
			$this->assertTrue(true);
		}
		catch(\ErrorException $ex) {
			$this->assertTrue(false, $ex->getMessage());
		}
	}

	public function testRunPhpCodeBackgroundReal() {
		try {
			$data = zot\proc\random_chars(32);
			Process::runPhpCodeBg("file_put_contents('test_phpcodebg', '$data');", 3);
			$readData = Process::runPhpCode("print file_get_contents('test_phpcodebg');unlink('test_phpcodebg');");
			if($data !== $readData) {
				$readData = Process::runPhpCode("print file_get_contents('test_phpcodebg');unlink('test_phpcodebg');", 5);
				if($data === $readData) {
					$this->assertTrue(true);
				}
				else {
					$this->assertTrue(false);
				}
			}
			else {
				$this->assertTrue(false, "Background runs synchronously");
			}
		}
		catch(\ErrorException $ex) {
			$this->assertTrue(false, $ex->getMessage());
		}
	}

	public function testPrintAvailableRunners() {
		$runners = Process::getAvailableRunners();
		if($runners) {
			$this->assertTrue(true);
			var_dump($runners);
		}
		else {
			$this->assertTrue(false);
		}
	}
}

(new Tests())->testRunPhpCode();
(new Tests())->testRunPhpCodeBackgroundReal();
//(new Tests())->testPrintAvailableRunners();

//var_dump(Process::runPhpFileBg("e:/propheth/repo/commerceos/includes/plugins/core/email/ProcessQueuedEmails.php"));
//var_dump(Process::runPhpCode("print file_get_contents(\"http://www.testingmcafeesites.com/testcat_ac.html\");"));

//var_dump((new HttpProcessRunner())->run("http://localhost/commerceos/includes/plugins/core/email/public/ProcessQueuedEmails.php"));
//require_once(__DIR__."/../../zot/init.php");
//var_dump(Process::runPhpFileBg(\zot\app_path("plugins/core/email/ProcessQueuedEmails.php")));

?>