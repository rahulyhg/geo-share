<?php
	// Refresh to index    
	echo '<meta http-equiv="refresh" content="0; url=./" />';

	// Establish sql db conn
	require_once('/home/cs0/joslcarp/config.php');
	$conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Post from hidden form
	if(isset($_POST['submit'])){
		$userName 	= trim($_POST['Username']);
		$fullName 	= trim($_POST['Fullname']);
		$latCoord 	= trim($_POST['Latcoord']);
		$longCoord 	= trim($_POST['Longcoord']);
		$imageURL 	= trim($_POST['Imageurl']);

		// Sql query to delete prexisting and insert current user data to sql db
		if($userName != NULL){
			mysqli_query($conn, "DELETE FROM Friendshare WHERE Username = '$userName' ");
		  	mysqli_query($conn, "INSERT INTO Friendshare (Username, Fullname, LatCoord, Longcoord, Imageurl)
	    		VALUES ('$userName', '$fullName', '$latCoord', '$longCoord', '$imageURL')");
		}
	}

	$conn->close();
?>
