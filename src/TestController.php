<?php
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

$db = "sqlite:app/data/db.sqlite";

$finder = new Finder(); 
$getID3 = new getId3;
$finder->files()->in('output');
$base_dir = getcwd();
	
foreach ($finder as $file) {
	$file_path = $base_dir ."\\".$file;
	$ThisFileInfo = $getID3->analyze($file_path);
	var_dump($ThisFileInfo);
	die();
}*/
class TestController extends \Slimfra\Controller {
	
	public function test() {
		$response = new StreamedResponse();
		$response->setCallback(function () {
			echo 'Hello World';
			flush();
			sleep(2);
			echo 'Hello World';
			flush();
		});
		$response->send();
	}
}


