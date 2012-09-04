<?php

/*
 * A class used for dealing with the sqlite database
 * Has methods to update, delete from, insert into, and get from the database
 * Uses the PDO object to help sanitize the sql queries by binding parameters
 */

class DatabaseAccess {
	private $db;
	private $sql_file;
	
	//Constructor uses the given sql_file string to create the database connection
	public function __construct($sql_file) {
		$this->sql_file = $sql_file;
		$this->setDatabase();
	}
	
	//Updates the database result data for a given id
	public function updateSqlite($id, $encoded_data) {
		$query_update = "UPDATE transcodes
			SET	result= :encoded_data, status='transcoded'
			WHERE id= :id;";
		$stmt = $this->db->prepare($query_update);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->bindParam(":encoded_data", $encoded_data, PDO::PARAM_STR);
		$result = $stmt->execute();
		return $result;
	}
	
	//Inserts the given infile, preset key, and outfile into the database in addition to constants for a 'scheduled' job
	public function insertIntoSqlite($infile, $preset_key, $outfile) {
		$outfile = trim($outfile);
		$infile = trim($infile);
		$status = "scheduled";
		$result = "null";
		
		$query_insert = "INSERT 
			INTO transcodes
			(infile, preset_key, outfile, status, result)
			VALUES (:infile,:preset_key,:outfile,:status,:result);";
			
		$stmt = $this->db->prepare($query_insert);
		$stmt->bindParam(':infile', $infile, PDO::PARAM_STR);
		$stmt->bindParam(':preset_key', $preset_key, PDO::PARAM_STR);
		$stmt->bindParam(':outfile', $outfile, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':result', $result, PDO::PARAM_STR);
		$result = $stmt->execute();
		
		return $this->db->lastInsertId();
	}
	
	//Gets all the information from the database for a given id
	public function getFromSqlite($id) {
		$query_select = "
			SELECT infile,preset_key,outfile,status,result
			FROM transcodes
			WHERE id = :id";
		$stmt = $this->db->prepare($query_select);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$result = $stmt->execute();
		$info = $stmt->fetch(PDO::FETCH_ASSOC);
		return $info;
	}
	
	//Gets all the information from the database for a given outfile-which should theoretically be unique
	public function getFromSqliteUsingFile($outfile) {
		$outfile = trim($outfile);
		$query_select = "SELECT id,result,infile
			FROM transcodes
			WHERE outfile= :outfile;";
		$stmt = $this->db->prepare($query_select);
		$stmt->bindParam(":outfile", $outfile, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result;
    }
	
	//Deletes an entry from the database with the given id
	public function delete($id) {
		try {
			$query_delete = "DELETE 
				FROM transcodes
				WHERE id=:id;";
			$stmt = $this->db->prepare($query_delete);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
				
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	
	//Inserts a custom preset into the database
	public function insertPreset($key, $adapter, $options) {
		$query_insert = "INSERT 
			INTO presets
			(key, adapter, options)
			VALUES (:key, :adapter, :options)
			;";
			$stmt = $this->db->prepare($query_insert);
			$stmt->bindParam(":key", $key, PDO::PARAM_STR);
			$stmt->bindParam(":adapter", $adapter, PDO::PARAM_STR);
			$stmt->bindParam(":options", $options, PDO::PARAM_STR);
			$stmt->execute();
	}
	
	//Sets the database- used in the constructor
	public function setDatabase() {
		try {
			$this->db = new PDO($this->sql_file);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
	}
}