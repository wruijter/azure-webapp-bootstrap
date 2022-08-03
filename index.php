<html>
	<head>
		<title>Azure WebApp Service Auth demo</title>
	</head>

	<body>
			<?php
				//Check if a valid user is logged in on the Azure Web App configured authentication
				if(isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME'])){

					// Find Approles related to the user
					$tokendata = base64_decode(explode(".",$_SERVER['HTTP_X_MS_TOKEN_AAD_ID_TOKEN'])[1]); //https://stackoverflow.com/questions/58105561/get-assigned-roles-in-azure-web-app-with-php
					$tokendataArray = json_decode($tokendata); //JSON string to PHP object

					// connect to database
					$con = mysqli_init();
					mysqli_ssl_set($con,NULL,NULL, "./cert/DigiCertGlobalRootCA.crt.pem", NULL, NULL);
					mysqli_real_connect($con, "xxxx.mysql.database.azure.com", "username", "password", "database", 3306, MYSQLI_CLIENT_SSL);
					
					// Do Some MySQL stuff if neccesery, my prefference is to do this in the included PHP files but to start the main connection here (managed from 1 place)
					//$result = mysqli_query($con,"SELECT * FROM `klanten`");
					//while ($row = mysqli_fetch_assoc($result)) {
					//	echo $row['KLANTEN'];
					//}

					// Filter ID
					$ID = $_GET['ID'];
					$ID = (int)preg_replace("/[^0-9]+/", "", $ID);

					//Serve pages, here begins the logic of asigning pages to specific roles
					if (($ID == "") && ((in_array("user",$tokendataArray->{'roles'})) || (in_array("admin",$tokendataArray->{'roles'})))){
						include './data/userpage.php';
					} 
					if (($ID == 1) && (in_array("Admin",$tokendataArray->{'roles'}))){ 
						include './data/adminpage.php';
					}  
					else{
						echo "<br><center><h1>Not found...</h1></center><br>";;
					}
					
					//close the connection
					mysqli_close($con);

				}
				//No valid user found, make a redirect to SAML login
				else {
					echo '<center>You will be redirected to log in....</center>';
					echo '<script>window.location.replace("./.auth/login/aad/callback");</script>';
				}

			?>

	</body>

</html>