<html>
<body>


	<?php  

	# Returns true if entry is marked as available
    function lineChecker($line) {
        $arr = explode(",", $line);
        if ($arr[6] == "unavailable\n" or $arr[6] == "unavailable") {
            return false;
        }
        return true;
    }

	# Formats and rounds the time given by HTML forms into an int
	function timeToInt($stringie){
		$temp = explode(":", $stringie);
		$num = $temp[0] + ($temp[1] / 60);
		return round($num,0);
	}

	# Selection sort
	function sortie($arr){
		for ($i = 0; $i < count($arr)-1; $i++){
			$min = $i;
			for ($j = $i+1; $j < count($arr); $j++){
				if ($arr[$j][6] < $arr[$min][6]){
					$min = $j;
				}
			}

			if ($i!=$min){
				$temp = $arr[$i];
				$arr[$i] = $arr[$min];
				$arr[$min] = $temp;
			}
		}
		return $arr;
	}

	$username = $_POST["username"];
    $useremail = $_POST["useremail"];

	$parser = fopen("writable/record.csv", "r");
    $filearray = [];
	$matchingarray = [];
	$selfarray = [];

	#Populates $filearray with every line from the spreadsheet
	while (!feof($parser)){
        $filearray[] = fgets($parser);
    }

	fclose($parser);

	# Populates $selfarray with every available entry that matches the current user's email, and $matchingarray with every entry that doesn't
	for ($i = 0; $i < count($filearray); $i++){
		if (lineChecker($filearray[$i])) {
			if (str_contains($filearray[$i],$useremail)) {
				$selfarray[] = explode(",",$filearray[$i]);
			} else {
				$matchingarray[] = explode(",",$filearray[$i]);
			}
		}
	}

	# Sets column 7 (previously availability, which has served its purpose) to 0 in preperation for algorithmic scoring
	for ($i = 0; $i < count($matchingarray); $i++){
		$matchingarray[$i][6] = 0;
	}

	/* Scoring according to compatibility
	 Same day 					= + 10
	 Same day within 2 hours 	= + 5
	 Same day within 1 hour 	= + 10
	 Same day same hour			= + 15
	 Same subject 				= + 5
	 Same subject same class 	= + 10
	*/
	
	for ($i = 0; $i < count($selfarray); $i++){
		for ($j = 0; $j < count($matchingarray); $j++){
			if ($selfarray[$i][2]==$matchingarray[$j][2]){
				$matchingarray[$j][6]+=10;
				$diff = abs(timeToInt($selfarray[$i][3])-timeToInt($matchingarray[$j][3]));
				if ($diff==0){
					$matchingarray[$j][6]+= 15;
				} else if ($diff<= 1){
					$matchingarray[$j][6]+= 10;
				} else if ($diff<= 2){
					$matchingarray[$j][6]+= 5;
				}
			}

			if ($selfarray[$i][4]===$matchingarray[$j][4]){
				$matchingarray[$j][6]+=5;
				if ($selfarray[$i][5]==$matchingarray[$j][5]){
					$matchingarray[$j][6]+= 10;
				}
			}
		}
	
	}

	# Prints current user's own availability (for easy reference) 
	echo "<b>YOUR AVAILABILITY:</b><br>";
	for ($i = 0; $i < count($selfarray); $i++){
		echo $selfarray[$i][2].' '.$selfarray[$i][3].'<br>'.$selfarray[$i][4].' '.$selfarray[$i][5].'<br><br>';
	}

	$omit=0; # In preparation for filtering poor matches
	$matchingarray=sortie($matchingarray); # Sort every entry by score
    
	# Iterates through the sorted $matchingarray backwards (highest scores first)
	# and prints match compatibility, name, email, subject, class, day, time, and match score of each entry
	# omitting any entry that scored below 15 points
	for ($i = count($matchingarray)-1; $i >=0; $i--){
		if ($matchingarray[$i][6]>=30){
	    	echo '<b>EXCELLENT MATCH: </b>';
		} elseif ($matchingarray[$i][6]>= 20){
			echo '<b>GOOD MATCH: </b>';
		} elseif ($matchingarray[$i][6]>= 15){
			echo '<b>MATCH: </b>';
		} else {
			$omit++;
			continue;
		}
		echo $matchingarray[$i][1].'<br>'.$matchingarray[$i][0].'<br>';
		echo $matchingarray[$i][2].' '.$matchingarray[$i][3].'<br>'.$matchingarray[$i][4].' '.$matchingarray[$i][5];
		echo '<br>Match score: '.$matchingarray[$i][6];
		echo '<br><br>';
	}

	echo $omit.' poor match(es) omitted.';
	
	?>

	<!-- Returns user to previous (edit) page -->
	<br><br><br>
	<form action="project2.php" method=post>
        <input type="hidden" name="useremail" value="<?php echo $useremail?>">
        <input type="hidden" name="username" value="<?php echo $username?>">
		<input type="submit" value="Return to Previous">    
    </form>

	<!--Returns user to login page -->
	<form action="project.php">
        <input type="submit" value="Return to Login">
    </form>
</body>
</html>