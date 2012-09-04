<?php

use Symfony\Component\HttpFoundation\Request;

require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

/** Controller to access databases with PDO object
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class DatabaseController extends \Slimfra\Controller {

	private $db;
	
	public function setDatabase($filename) {
		try {
			$this->db = new PDO($filename);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}
	
	public function insertIntoSqlite() {
		$filename = "sqlite:app/data/db.sqlite";
		$this->setDatabase($filename);
		
		$outfile = $this['request']->request->get('outfile');
		$infile = $this['request']->request->get('infile');
		$outfile = "output\\" . trim($outfile);
		$infile = "input\\" . trim($infile);
		
		$getID3 = new getID3;
		
		$analyze_outfile_path = getcwd()."//".$outfile;
		$ThisFileInfo = $getID3->analyze($analyze_outfile_path);
		$out_size = $ThisFileInfo['filesize'];
		$out_name = $ThisFileInfo['filename'];
		
		$analyze_infile_path = getcwd()."//".$infile;
		$ThisFileInfo = $getID3->analyze($analyze_infile_path);
		$in_size = $ThisFileInfo['filesize'];
		$in_name = $ThisFileInfo['filename'];
		$insert_data = array('infile_name' => $in_name, 'infile_size' => $in_size, 'outfile_name' => $out_name, 'outfile_size' => $out_size);
		$encoded_data = json_encode($insert_data);
		
		$query_insert = "INSERT 
			INTO transcodes
			(file, data)
			VALUES (:outfile,:data)
			;";
			
		$stmt = $this->db->prepare($query_insert);
		$stmt->bindParam(':outfile', $outfile, PDO::PARAM_STR);
		$stmt->bindParam(':data',$encoded_data);
		$result = $stmt->execute();
		
		return $encoded_data;
	}
    /*public function getFromSqlite() {
		$filename = "sqlite:app/data/db.sqlite";
		$this->setDatabase($filename);
		
		$outfile = $this['request']->query->get('outfile');
		$outfile = trim($outfile);
		
		$query_select = "SELECT data 
			FROM transcodes 
			WHERE file = :outfile
		;";
		$stmt = $this->db->prepare($query_select);
		$stmt->bindParam(':outfile', $outfile, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return $result["data"];
    }*/
}

