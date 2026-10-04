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

    # Returns a human-readable string with the name and email removed
    function lineFormatter($line){
        $arr = explode(",", $line);
        array_shift($arr);
        array_shift($arr);
        array_pop($arr);
        $finalLine = implode(" ", $arr);
        return $finalLine;
    }

	$username = $_POST["username"];
    $useremail = $_POST["useremail"];
    echo "<h3> Welcome, $username.\n</h3>";

	?>
    
    <!-- Takes in data for a new availability entry, then reloads page for processing -->
    <form method="post"> 
        <h1>What's your availability?</h1>

        <input type="hidden" name="username" value="<?php echo $username?>">
        <input type="hidden" name="useremail" value="<?php echo $useremail?>">


		<input type="radio" name="availday" value="Sunday">
        <label for="Sunday">Sunday</label>
        <input type="radio" name="availday" value="Monday">
        <label for="Monday">Monday</label>
        <input type="radio" name="availday" value="Tuesday">
        <label for="Tuesday">Tuesday</label>
        <input type="radio" name="availday" value="Wednesday">
        <label for="Wednesday">Wednesday</label>
        <input type="radio" name="availday" value="Thursday">
        <label for="Thursday">Thursday</label>
        <input type="radio" name="availday" value="Friday">
        <label for="Friday">Friday</label>
        <input type="radio" name="availday" value="Saturday">
        <label for="Saturday">Saturday</label>

		<br><br>
		<input type="time" name="availtime">
		<br>

        <h1>What class do you need help with?</h1>
        <select name="dept">
            <option value="CS">CS</option>
            <option value="ART">ART</option>
            <option value="ENGR">ENGR</option>
            <option value="HIST">HIST</option>
            <option value="MATH">MATH</option>
            <option value="MUSC">MUSC</option>
            <option value="PHYS">PHYS</option>
            <option value="UNIV">UNIV</option>
        </select>
         
        <input type="text" name="course" ><br><br>

	<input type="submit" value="Submit">
    </form>

    <?php

        # Checks $_POST for any new entry data, and if it exists, appends it to the spreadsheet

        if (!empty($_POST["availday"]) and !empty($_POST["availtime"]) and !empty($_POST["dept"])and !empty($_POST["course"])){

            $record = fopen("writable/record.csv", "a") or die("Failed to open file.");
            
            $availday = $_POST["availday"];
            $availtime = $_POST["availtime"];
            $dept = $_POST["dept"];
            $course = $_POST["course"];

            fwrite($record, "\n$useremail,$username,$availday,$availtime,$dept,$course,available");
            fclose($record);
        }

        # Checks $_POST for any lines to be set to unavailable, and if it exists, unavailables it

        if ($_POST["deleteLine"]){
            $deleteLine=$_POST["deleteLine"];

            $parser = fopen("writable/record.csv", "r+") or die("Failed to open file.");
            $filearray = [];

            # Takes in every line previously in the file as an array
            while (!feof($parser)){
                $filearray[] = fgets($parser);
            }

            # Opens the file in write mode to destroy all the previous data
            fclose($parser);
            $cleaner= fopen("writable/record.csv", "w");
            fclose($cleaner);

            # Fines the offending line in the array and sets it to unavailable
            for ($i = 0; $i < count($filearray); $i++){
                if ($filearray[$i] == $deleteLine){
                    $temp = explode(",",$filearray[$i]);
                    $temp[6]="unavailable\n";
                    $filearray[$i] = implode(",",$temp);
                }
            }

            # Rewrites everything back into the file along with the modified line
            $record = fopen("writable/record.csv","a");

            for ($j = 0; $j < count($filearray); $j++){
                fwrite($record,$filearray[$j]);
            }
            fclose($record); 
        }

        # Populates the array $existing with entries in the spreadsheet that matches the current user's email
        $record = fopen("writable/record.csv", "r");
        $existing = [];

        while (!feof($record)){
            $line = fgets($record);
            if (str_contains($line, $useremail)){
                if (lineChecker($line)){
                    $existing[] = $line;
                }
                
            }
        }
        fclose($record);

        
        if ($existing){

            ?>

            <!-- Matching button to proceed to the matchmaking page. 
            Placed here to ensure users without existing availabilities cannot proceed -->
            <br><br><br><br><br><br>
            <form action="project3.php" method="post">
                <input type="hidden" name="useremail" value="<?php echo $useremail?>">
                <input type="hidden" name="username" value="<?php echo $username?>">
                <input type="submit" value="Match me!">
            </form>

            <!-- Form for editing the user's availabilities  -->
            <br><br><br><br><br><br>
            <form method=post>
                <input type="hidden" name="useremail" value="<?php echo $useremail?>">
                <input type="hidden" name="username" value="<?php echo $username?>">
                
                <?php
                for ($i = 0; $i < count($existing); $i++){
                    if (lineChecker($existing[$i])){
                        echo '<br><input type="radio" name="deleteLine" value="'.$existing[$i].'">';
                        echo '<label for="'.$existing[$i].'">'.lineFormatter($existing[$i]).'</label>';
                    }
                }
                ?>

                <br>
                <input type="submit" value="Mark Unavailable">    
            </form>
            <?php
        }

        
            
    
    ?>

    <!-- Returns user to previous (login) page -->
    <form action="project.php">
        <input type="submit" value="Return to Login">
    </form>
                
		


</body>
</html>