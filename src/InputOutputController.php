<?php

use AC\Mutate\Transcoder;
use AC\Component\Transcoding\MimeMap;
use AC\Component\Transcoding\Preset\Handbrake\ClassicPreset;
use AC\Component\Transcoding\File;
use AC\Component\Transcoding\FileHandlerDefinition;
use Symfony\Component\Finder\Finder;

require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

/**
 * A controller that sets up the home page for the Mutate application.  See `index.php` for the routes mapped to these methods.
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class InputOutputController extends \Slimfra\Controller {

	private $handbrake_path = "C:\\Users\\Andrew\\Downloads\\HandBrake-0.9.6-x86_64-Win_CLI\\HandbrakeCLI.exe";
	private $ffmpeg_path = "C:\\Users\\Andrew\\Downloads\\ffmpeg-20120720-git-85761ef-win64-static\\ffmpeg-20120720-git-85761ef-win64-static\\bin\\ffmpeg.exe";

	public function display() {
		$transcoder = new Transcoder(array(
			'transcoder.handbrake.enabled' => true,
			'transcoder.ffmpeg.enabled' => true,
			'transcoder.handbrake.path' => $this->handbrake_path,
			'transcoder.ffmpeg.path' => $this->ffmpeg_path,
		));
		$available_presets = $transcoder->getPresets();
		$allowed_presets = array();
		
		$infile_finder = new Finder();
		$outfile_finder = new Finder();
		$infile_finder->files()->in('input');
		
		$sql_file = "sqlite:app/data/db.sqlite";
		$db_access = new DatabaseAccess($sql_file);
		
		$base_dir = $this['cur_dir'];
		$infile_data = array();
		
		//Checks which presets are usable for each input file
		foreach ($infile_finder as $infile) {
			$filename = $base_dir .'\\'. $infile;
			$rel_path = $infile->getRelativePathname();
			$infile_data[] = $rel_path;
			$test_file = new File($filename);
			foreach($available_presets as $preset) {
				if($preset->acceptsInputFile($test_file)) {
					//Checks to see if the current preset has a set output extension or if the user needs to supply one
					$output_file_definition = $preset->getOutputDefinition();
					$extension = $output_file_definition->getRequiredExtension();
					if($extension == false) {
						$extension = "null";
					} 
					if(isset($allowed_presets[$rel_path])){
						$allowed_presets[$rel_path][] = $preset->getKey(); 
						$allowed_presets[$rel_path][] = $extension;
					} else {
						$allowed_presets[$rel_path] = array($preset->getKey());
						$allowed_presets[$rel_path][] = $extension; 
					}
				} 
			}
		}
		$allowed_presets = json_encode($allowed_presets);
		
		//Generate popover content from the database for the output files
		$getID3 = new getId3;
		$outfile_finder->files()->in('output');
		$outfile_data = array();
		foreach ($outfile_finder as $outfile) {
			$outfile_data[$outfile->getRelativePathname()] = array('id' => '', 'data' => '');
			$result = $db_access->getFromSqliteUsingFile($outfile);
			$data = json_decode($result['result']);
			if($result!= null){
				if($data == null) { //A precaution if there is a output file in the file system, that somehow has no data in the database
					$outfilePath = $base_dir."\\".$outfile;
					$infile = $result['infile'];
					$infilePath = $base_dir."\\".$infile;
					//GetID3 retrieves the filesize and filename of both the input and output files 
					$ThisFileInfo = $getID3->analyze($outfilePath);
					$out_size = $ThisFileInfo['filesize'];
					$out_name = $ThisFileInfo['filename'];
					$ThisFileInfo = $getID3->analyze($infilePath);
					$in_size = $ThisFileInfo['filesize'];
					$in_name = $ThisFileInfo['filename'];
					//End GetID3 analysis
					$insert_data = array('infile_name' => $in_name, 'infile_size' => $in_size, 'outfile_name' => $out_name, 'outfile_size' => $out_size);
					$encoded_data = json_encode($insert_data);
					//Updates the database with the data that should have been there
					$db_access->updateSqlite($result['id'], $encoded_data);
				} else { //Data is sent to the page so that each output file's popover is filled with the correct information and that each output file knows its own ID
					$data_text = "Infile Name: ".$data->infile_name."<br>";
					$data_text .= "Infile Size: ".$data->infile_size."<br>";
					$data_text .= "Outfile Name: ".$data->outfile_name."<br>";
					$data_text .= "Outfile Size: ".$data->outfile_size;
				}
				$outfile_data[$outfile->getRelativePathname()]['data'] = $data_text; //File information
				$outfile_data[$outfile->getRelativePathname()]['id'] = $result['id']; //Output ID
			}
		}
		
		//Generate the base route_url for the ajax calls
		$request = $this['request'];
		$appRoute = "";
		$route_url = $request->getScheme() . "://". $request->getHost() .	$request->getBasePath() . $appRoute;
		
		//Sends all the processed data to a twig template to display the page
		return $this['twig']->render('input_output.html', array(
			'input' => $infile_data, //The array of input files
			'output' => $outfile_data, //The hashed array of output files to their corresponding data and id
            'app_name' => $this['app_name'], 
			'base_path' => json_encode($this['request']->getBasePath()),
			'route_url' => json_encode($route_url),
			'cur_dir' => $this['cur_dir'],
			'max_file_size' => $this['max_file_size'],
			'allowed_presets' => $allowed_presets, //The hashed array of input files to the presets that accepted the given input file and which presets require user-supplied extensions
        ));
	}
}