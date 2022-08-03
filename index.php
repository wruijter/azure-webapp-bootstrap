<html>
	<head>
		<title>Azure WebApp Service Auth demo</title>
	</head>

	<body>
			<?php
				//Check if a valid user is logged in on the Azure Web App configured authentication, https://docs.microsoft.com/nl-nl/azure/app-service/overview-authentication-authorization
				if(isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME'])){
					
					// App registration is OK and token is available
					if(isset($_SERVER['HTTP_X_MS_TOKEN_AAD_ID_TOKEN']))){
						
						// Find Approles related to the user
						$tokendata = base64_decode(explode(".",$_SERVER['HTTP_X_MS_TOKEN_AAD_ID_TOKEN'])[1]); //https://stackoverflow.com/questions/58105561/get-assigned-roles-in-azure-web-app-with-php
						$tokendataArray = json_decode($tokendata); //JSON string to PHP object
						$rolesAssigned = $tokendataArray->{'roles'}; //https://github.com/MicrosoftDocs/azure-docs/blob/main/articles/active-directory/develop/howto-add-app-roles-in-azure-ad-apps.md
						
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
						$pageID = $_GET['ID'];
						$pageID = (int)preg_replace("/[^0-9]+/", "", $pageID);

						//Serve pages, here begins the logic of asigning pages to specific roles
						if (($pageID == "") && ((in_array("user",$rolesAssigned)) || (in_array("admin",$rolesAssigned)))){ //if role user or admin is assigned
							include './data/userpage.php';
						} 
						else if (($pageID == 1) && (in_array("admin",$rolesAssigned))){  //if role admin is assigned
							include './data/adminpage.php';
						}  
						else{
							echo "<br><center><h1>Error</h1></center><br>";;
						}
						
						//close the connection
						mysqli_close($con);
					}
					// Something definitly gone wrong....
					else{
						echo "<center>User logged in but token not available....</center>";
					}

				}
				//No valid user found, make a redirect to SAML login
				else {
					echo '<center>You will be redirected to log in....</center>';
					echo '<script>window.location.replace("./.auth/login/aad/callback");</script>';
				}

			?>

	</body>

</html>
