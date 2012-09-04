<?php
set_time_limit(0);

function average($arr) {
	$sum = 0.0;
	for($i = 0; $i < count($arr); $i++) {
		$sum+=$arr[$i];
	}
	
	return ($sum/count($arr));
}


$skill_levels_and_weights = array();

for($w = 10; $w > 0; $w--){
	$skill_levels_and_weights[] = $w;
}

$example_videos = array();
 for($d = 0; $d < 10; $d++) {
	$example_videos[] = rand(1,100);
 }

 $example_users = array();
 for($u = 0; $u < 100; $u++) {
	$example_users[] = rand(1,10);
 }

echo("<pre>".print_r($example_videos,true)."</pre>");
echo("<pre>".print_r($example_users,true)."</pre>");
 
$trace = array(); 
//for($i=0;$i<count($example_videos);$i++) {
for($i=0;$i<1;$i++) {
	$teacher_start = $example_videos[$i];
	$difficulty = $teacher_start;
	$trace[$teacher_start] = array();
	$ratings = array();
	$errors = array();
	for($x=0; $x<10;$x++) {
		$ratings[] = $teacher_start;
	}
	//for($j=0;$j<100;$j++) {
		echo("Starting difficulty: ".$teacher_start."<br>");
		for($k = 0; $k <count($example_users); $k++){
			$trace[$teacher_start][] = $difficulty;
			//$error = (0.2 * (abs(($example_users[$k]*10.0)-$difficulty)/10));
			$error = 1;
			if(isset($errors[$example_users[$k]])) {
				$errors[$example_users[$k]][] = $error;
			} else {
				$errors[$example_users[$k]] = array($error);
			}
			$over_or_under = rand(1,2);
			if($over_or_under == 1) {
				$error *= -1;
			}
			$rating = intval($teacher_start/$example_users[$k])+$error;
			$rated_diff = $rating*$example_users[$k];
			//$rated_diff += 5*$error;
			if($rated_diff > 10*$example_users[$k]) {
				$rated_diff = 10*$example_users[$k];
			} elseif($rated_diff < $example_users[$k]) {
				$rated_diff = $example_users[$k];
			}
			$ratings[] = $rated_diff; 
			$difficulty = average($ratings);
			echo($difficulty.">");
			for($p=0; $p<($difficulty);$p++) {
				echo("-");
			}
			echo("User level: ".$example_users[$k]." Rating: ".$rated_diff);
			echo("<br>");
		}
	//}
	echo("<pre>".print_r($errors,true)."</pre>");
}