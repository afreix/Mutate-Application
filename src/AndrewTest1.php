<?php
use Symfony\Component\Finder\Finder;

require_once ('C:\xampp\htdocs\GetID3\getid3-1.9.3-20111213\getid3\getid3.php');

/** Andrew's first test controller
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class AndrewTest1 extends \Slimfra\Controller {

	public function deleteFile() {
		$filename = "output\\test.txt"; 
		unlink($filename);
	}
	public function insertTest() {
		$db = "sqlite:app/data/db.sqlite";
		try {
			$dbh = new PDO($db);
			$query_insert = "INSERT INTO presets(key, adapter, options)
				VALUES ('test_key','test_adapter','test_options')	
				;";
			$stmt = $dbh->prepare($query_insert);
			$return = $stmt->execute();
			echo $return;
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	public function testPreset() {
		$db = "sqlite:app/data/db.sqlite";

		try {
			
			$dbh = new PDO($db);
			
			$query_select = "SELECT *
				FROM presets
				WHERE 1
			;";
			
			$outfile = "output\movie.handbrake.classic.mp4";
			//$stmt = $dbh->prepare($query_select);
			$result = $dbh->query($query_select);
			foreach ($result as $row) {
				echo $row['key'];
				echo "<br>";
				echo $row['adapter'];
				echo "<br>";
				echo $row['options'];
				echo("<br>");
			}
			
			//echo $result["data"];
			//print(json_encode($result));
			
			$dbh = null;
			
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	public function deletePreset() {
		$db = "sqlite:app/data/db.sqlite";

		try {
			
			$dbh = new PDO($db);
			$query_delete = "DELETE 
				FROM presets
				WHERE 1;";
			$delete = $dbh->query($query_delete);
				
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	public function test() {
		$db = "sqlite:app/data/db.sqlite";

		try {
			
			$dbh = new PDO($db);
			$query_insert = "INSERT INTO transcodes (infile,preset)
				VALUES ('test_file','test_data')	
				;";
			
			$query_select = "SELECT *
				FROM transcodes 
				WHERE 1
			;";
			
			$outfile = "output\movie.handbrake.classic.mp4";
			//$stmt = $dbh->prepare($query_select);
			$result = $dbh->query($query_select);
			foreach ($result as $row) {
				echo $row['id'];
				echo "<br>";
				echo $row['infile'];
				echo "<br>";
				echo $row['preset_key'];
				echo "<br>";
				echo $row['outfile'];
				echo "<br>";
				echo $row['status'];
				echo "<br>";
				echo $row['result'];
				echo "<br>";
			}
			
			//echo $result["data"];
			//print(json_encode($result));
			
			$dbh = null;
			
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	public function delete() {
		$db = "sqlite:app/data/db.sqlite";

		try {
			
			$dbh = new PDO($db);
			$query_delete = "DELETE 
				FROM transcodes
				WHERE 1;";
			$delete = $dbh->query($query_delete);
				
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
}
?>

<!--<html>
	<head>
		<script type ="text/javascript" src ="http://code.jquery.com/jquery-latest.js"> </script>
		<link type="text/css" href="JQuery_UI/css/themename/jquery-ui-1.8.14.custom.css" rel="Stylesheet" />
		<script type="text/javascript" src="http://code.jquery.com/ui/1.8.14/jquery-ui.js"></script>
		<script type="text/javascript">
			$("button").live('click', function(){
				alert("hi");
			});
		</script>
	</head>
	<body>
		<div>
			Hey
		</div>
		<button class="hover">
			Hover
		</button>
	</body>
</html>-->

