<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

/** Controller to deal with file upload and deletions
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class FileController extends \Slimfra\Controller {
	
	//Upload input files from the application
	public function uploadFile() {
		$base_dir = $this['cur_dir'];
		$request = $this['request'];
		$appRoute = "/";
		
		ini_set('upload_max_filesize', $this['max_file_size']); 
		$uploadFiles = $this['request']->files->get("files");
		
		if($uploadFiles == null || $uploadFiles[0] == null) {
			//Currently doesn't redirect because the user wouldn't see the message
			die ("I'm sorry. An error occured while uploading your file. It could be above the max file size of ". $this['max_file_size'].".");
		} else { 
			for($i = 0; $i<count($uploadFiles); $i++) { //Iterates over all the files and adds them into the input folder
				$fileName = $uploadFiles[$i]->getClientOriginalName();
				$fileName = preg_replace("/[^a-zA-Z0-9\s\'\-]/", ".", $fileName);
				$dir = $base_dir . "\\input";
				$uploadFiles[$i]->move($dir, $fileName);
			}
			$url = $request->getScheme() . "://". $request->getHost() .	$request->getBasePath() . $appRoute;
			return new RedirectResponse($url); //Reloads the page which will now have the new files
		}
	}
	
	//Deletes the file from the computer and database (if necessary)
	public function deleteFile() {
		$filename = "";
		$file = $this['request']->request->get('file');
		$id = $this['request']->request->get('id');
		if($this['request']->request->get('infile') == 'true') {
			$filename = "input\\";
			$filename .= $file;
		} else { //If it is an output file it needs to be removed from the database as well
			$filename .= "output\\";
			$filename .= $file;
			$sql_file = "sqlite:app/data/db.sqlite";
			$db_access = new DatabaseAccess($sql_file);
			$db_access->delete($id);
		}
		unlink($filename);
		
		return $filename . " INFILE: " .$this['request']->request->get('infile');
	}
}

