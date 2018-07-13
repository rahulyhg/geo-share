<!DOCTYPE html>
<html>
<head>
<title>Geo Share</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<!-- Latest compiled and minified CSS -->
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<!-- Lobster font family -->
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet">
<style>
	body {background-color: #FCFBE3 !important;}
	
	.background-white {background-color: white;}

	.btn.btn-square {border-radius: 0;}

	.title-lobster {
		font-family: 'Lobster', cursive;
		color: red;
	}
</style>
</head>

<?php   
	// Configure sql connection for db
	require_once('/home/cs0/joslcarp/config.php');

	$conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
?>

<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-6">
				<h4 class="title-lobster">&nbsp;&nbsp;Friend-Share&nbsp;&nbsp;</h4>
			</div>
			<!-- Google login / Map check-in -->
			<div class="col-xs-3" role="group">
				<script src="https://apis.google.com/js/platform.js" async defer></script>				
				<meta name="google-signin-client_id" content="205523504362-13tcr7nmdnqj3qii2me61uvo0u0leses.apps.googleusercontent.com">
				<div class="g-signin2" data-height="38px" data-onsuccess="onSignIn"></div>
			</div>
			<!-- Hidden form to post to sql using updateUser.php -->
			<div class="col-xs-3">
				<form action="updateUser.php" method="post">
					<input type="hidden" id="Username" name="Username" value="NULL">
					<input type="hidden" id="Fullname" name="Fullname" value="NULL">
					<input type="hidden" id="Latcoord" name="Latcoord" value="0">
					<input type="hidden" id="Longcoord" name="Longcoord" value="0">
					<input type="hidden" id="Imageurl" name="Imageurl" value="NULL">
					<input type="submit" class ="btn btn-primary btn-square" name="submit" value="Check In">
				</form>
			</div>
		</div>
		
		<?php
			// Retrieve data from db to prep for json conversion for map marker generation
			$result = mysqli_query($conn, "SELECT * FROM Friendshare WHERE Username != 'NULL'");

			while($row = mysqli_fetch_assoc($result)){
				$json[] = $row;	
			}
		?>

		<!-- GOOGLE MAP AND GEOLOCATION -->
		<div class="row">
			<div class="col-xs-12 col-md-12">
				<div id="map" style="width:346px;height:300px;"></div>
			    <script>
			      	// If you see the error "The Geolocation service failed.", it
			      	// means you probably did not give permission for the browser to
			      	// locate you.
			      	var map, infoWindow;
			      	function initMap() {
				        map = new google.maps.Map(document.getElementById('map'), {
				        	// Coordinates for Martin, TN
				        	center: {lat: 36.343, lng: 88.850},
				        	zoom: 12
				        });
				        infoWindow = new google.maps.InfoWindow;

				        // Try HTML5 geolocation.
				        if (navigator.geolocation) {
				        	navigator.geolocation.getCurrentPosition(function(position) {
				          		var pos = {
				              			lat: position.coords.latitude,
				              			lng: position.coords.longitude
				            		};

				          		//Lat and long coords for hidden form
				            		document.getElementById("Latcoord").value = pos.lat;
					    		document.getElementById("Longcoord").value = pos.lng;

				            		infoWindow.setPosition(pos);
				            		infoWindow.setContent('Location found.');
				            		infoWindow.open(map);
				            		map.setCenter(pos);
				          	}, function() {
				            		handleLocationError(true, infoWindow, map.getCenter());
				          	});
				        } 
				        else {
				          	// Browser doesn't support Geolocation
				          	handleLocationError(false, infoWindow, map.getCenter());
				        }

				        // Convert php array to json
				        var map_data = <?php echo json_encode($json); ?>;
					//console.log(JSON.stringify(map_data));

					// Generate markers from db using map_data for metadata
					Object.keys(map_data).forEach(key => {
			    			console.log(map_data[key]);

			    			var location = {
			    				lat: parseFloat(map_data[key]['Latcoord']),
							lng: parseFloat(map_data[key]['Longcoord'])
			    			};

			    			var image = {
			    				url: map_data[key]['Imageurl'],
			    				scaledSize: new google.maps.Size(32, 32),
			    				origin: new google.maps.Point(0, 0),
			    				anchor: new google.maps.Point(0, 0)
			    			};

			    			var marker = new google.maps.Marker({
			    				position: location,
			    				title: map_data[key]['Username'],
			    				map: map,
			    				icon: image
			    			});
			    		});
				    }

			      	function handleLocationError(browserHasGeolocation, infoWindow, pos) {
			        	infoWindow.setPosition(pos);
			        	infoWindow.setContent(browserHasGeolocation ?
			            	'Error: The Geolocation service failed.' :
			                'Error: Your browser doesn\'t support geolocation.');
			        	infoWindow.open(map);
			      	}			      		
			    </script>
			    <script async defer
			    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAPhbIPmXt8uHNbPXnD52NFywjniP4UT5s&callback=initMap">
			    </script>
			</div>
		</div>
		
		<!-- Chat client using socket.io and node.js hosted on Heroku -->
		<div class="row">
			<div class="col-xs-12 col-md-12">
				<iframe class="background-white" width="346" height="200" src="https://mighty-dusk-64300.herokuapp.com/"></iframe>
			</div>
		</div>

		<?php
			// Logging current contents of db into table
			$result = mysqli_query($conn, "SELECT * FROM Friendshare WHERE Username != 'NULL'");

			echo '<table class="table table-striped table-sm table-responsive">
			<thead class="thead-inverse">
			<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Name</th>
			<th>Latitude</th>
			<th>Longitude</th>
			<th>Image</th>
			</thead>
			<tbody class="background-white">';

			while($row = mysqli_fetch_assoc($result)){
				echo "<tr>";
				echo "<td>" . $row['id'] . "</td>";
				echo "<td>" . $row['Username'] . "</td>";
				echo "<td>" . $row['Fullname'] . "</td>";
				echo "<td>" . $row['Latcoord'] . "</td>";
				echo "<td>" . $row['Longcoord'] . "</td>";
				echo "<td><img src=\"" . $row['Imageurl'] . "\"></td>";
				echo "</tr>";
			}
			echo "</tbody></table>"; 

			$conn->close();
		?>

		<!-- Google sign-in -->
		<script>
			function onSignIn(googleUser) {
				// Useful data for your client-side scripts:
				var profile = googleUser.getBasicProfile();
				document.getElementById("Username").value = profile.getName();
				document.getElementById("Fullname").value = profile.getGivenName();
				// console.log('Family Name: ' + profile.getFamilyName());
				document.getElementById("Imageurl").value = profile.getImageUrl();
				// console.log("Email: " + profile.getEmail());

				// The ID token you need to pass to your backend:
				var id_token = googleUser.getAuthResponse().id_token;
				console.log("ID Token: " + id_token);
			};
		</script>
	</div>

	<!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
</body>
</html>
