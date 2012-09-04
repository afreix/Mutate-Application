<?php

use AC\Mutate\Transcoder;
use AC\Component\Transcoding\Event\TranscodeEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

/** Controller used with the Transcoding Application 
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class MutateController extends \Slimfra\Controller {
	
	private $handbrake_path = "C:\\Users\\Andrew\\Downloads\\HandBrake-0.9.6-x86_64-Win_CLI\\HandbrakeCLI.exe";
	private $ffmpeg_path = "C:\\Users\\Andrew\\Downloads\\ffmpeg-20120720-git-85761ef-win64-static\\ffmpeg-20120720-git-85761ef-win64-static\\bin\\ffmpeg.exe";

	//Schedules a transcode job
	public function scheduleJob() {
		$transcoder = new Transcoder(array(
			'transcoder.handbrake.enabled' => true,
			'transcoder.ffmpeg.enabled' => true,
			'transcoder.handbrake.path' => $this->handbrake_path,
			'transcoder.ffmpeg.path' => $this->ffmpeg_path,
			'transcoder.handbrake.timeout' => null,
			'transcoder.ffmpeg.timeout' => null,
		));
		
		//The default settings, but in all cases infile and preset_key should be supplied
		$inputFilePath = "input\\test.wmv";
		$outputFilePath = "output\\"; 
		$preset_key = "handbrake.classic";
		
		if ($this['request']->request->get('infile') != null) {
			$inputFilePath = "input\\" . $this['request']->request->get('infile');
		}
		if ($this['request']->request->get('preset_key') != null) {
			$preset_key = $this['request']->request->get('preset_key');
		}
		
		$dataArray = array('outfile' => '', 'id' => '');
		//Figures out what the output file should be called without actually transcoding the input file
		$newFile = $transcoder->getOutfilePath($inputFilePath, $preset_key, $outputFilePath, Transcoder::ONCONFLICT_INCREMENT, Transcoder::ONDIR_CREATE);
		
		//If the preset doesn't specify an extension in the buildOutputDefinition it uses the extension the user entered
		if($this['request']->request->get('outfile_extension') != 'null') {
			$newFile .= $this['request']->request->get('outfile_extension');
		}
		//Inserts into the sqlite database the input file, the preset key, and the output file
		//Constants: the status is set to 'scheduled', data is set to 'null', and id is autoincremented 
		$sql_file = "sqlite:app/data/db.sqlite";
		$db_access = new DatabaseAccess($sql_file);
		$id = $db_access->insertIntoSqlite($inputFilePath, $preset_key, $newFile);
		
		$dataArray['outfile'] = $newFile;
		$dataArray['id'] = $id;
		return json_encode($dataArray);
	}
	
	//Transcodes the input file
	public function transcodeUsingJob() {
		if($this['request']->request->get('id') != null) {
			$id = $this['request']->request->get('id');
			$sql_file = "sqlite:app/data/db.sqlite";
			$db_access = new DatabaseAccess($sql_file);
			//Get all the current data from the database based on the id
			$info = $db_access->getFromSqlite($id);
			
			$handbrake_path = "C:\\Users\\Andrew\\Downloads\\HandBrake-0.9.6-x86_64-Win_CLI\\HandbrakeCLI.exe";
			$ffmpeg_path = "C:\\Users\\Andrew\\Downloads\\ffmpeg-20120720-git-85761ef-win64-static\\ffmpeg-20120720-git-85761ef-win64-static\\bin\\ffmpeg.exe";
			$transcoder = new Transcoder(array(
				'transcoder.handbrake.enabled' => true,
				'transcoder.ffmpeg.enabled' => true,
				'transcoder.handbrake.path' => $this->handbrake_path,
				'transcoder.ffmpeg.path' => $this->ffmpeg_path,
				'transcoder.handbrake.timeout' => null,
				'transcoder.ffmpeg.timeout' => null,
			));
			//The input file, output file, preset_key, and status of a specific scheduled job
			$inputFilePath = $info['infile'];
			$outputFilePath = $info['outfile'];
			$preset_key = $info['preset_key'];
			$status = $info['status'];
			
			$messageArray = array();
			//Currently not working --8/28/12
			$transcoder->addListener(TranscodeEvents::MESSAGE, function($e) use (&$messageArray) {
				$messageArray[] .=  $e->getMessage() ."<br>";
			});
			//Streamed response currently can't give chunked responses -- 8/28/12
			$response = new StreamedResponse();
			$response->setCallback(function () use ($transcoder, $inputFilePath, $preset_key, $outputFilePath) {
				$newFile = $transcoder->transcodeWithPreset($inputFilePath, $preset_key, $outputFilePath, Transcoder::ONCONFLICT_INCREMENT, Transcoder::ONDIR_CREATE);
			});
			
			return $response;
		} else {
			die ("Could not retrieve ID.");
		}
	}
	
	//After completion of transcoding it updates the database and returns the result data
	public function updateAndRetrieve() {
		$id = $this['request']->request->get('id');
		$inputFilePath = $this['request']->request->get('infile');
		$outputFilePath = $this['request']->request->get('outfile');
		$getID3 = new getID3;
		//Gets the file information about the output file
		$analyze_outfile_path = getcwd()."//output//".$outputFilePath;
		if(file_exists($analyze_outfile_path)) { 
			$ThisFileInfo = $getID3->analyze($analyze_outfile_path);
			$out_size = $ThisFileInfo['filesize'];
			$out_name = $ThisFileInfo['filename'];
		
			//Gets file information about the input file
			$analyze_infile_path = getcwd()."//input//".$inputFilePath;
			$ThisFileInfo = $getID3->analyze($analyze_infile_path);
			$in_size = $ThisFileInfo['filesize'];
			$in_name = $ThisFileInfo['filename'];
			
			//Json_encodes the result data to be inserted into the database
			$insert_data = array('infile_name' => $in_name, 'infile_size' => $in_size, 'outfile_name' => $out_name, 'outfile_size' => $out_size);
			$encoded_data = json_encode($insert_data);
			
			$sql_file = "sqlite:app/data/db.sqlite";
			$db_access = new DatabaseAccess($sql_file);
			//Updates the database 
			$return = $db_access->updateSqlite($id, $encoded_data);
			//Gets all the information for the id from the database
			$result = $db_access->getFromSqlite($id);
			
			//Returns only the result data needed for the popover content
			return $result['result'];
		} else { //If the was an error in transcoding and the output file was never created
			return null;
		}
	}

	public function addPreset() {
		$conversion_array = array("audio-bitrate" => "-ab");
	
		$key = $this['request']->request->get('k');
		$adapter = $this['request']->request->get('a');
		$options = $this['request']->request->get('o');
		$encoded_options = array();
		foreach($options as $option_key => $value) {
			if(array_key_exists($option_key, $conversion_array) ) {
				$encoded_options[$conversion_array[$option_key]] = $value;
			}
		}
		$sql_file = "sqlite:app/data/db.sqlite";
		$db_access = new DatabaseAccess($sql_file);
		$db_access->insertPreset($key, $adapter, json_encode($encoded_options));
		return true;
	}
}

