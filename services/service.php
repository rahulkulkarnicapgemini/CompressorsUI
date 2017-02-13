<?php 

//Header for access
 header('Access-Control-Allow-Origin: *');  
 
$BaseURL = "https://c86b4112-3874-4da8-9850-088c89ea35fc-bluemix.cloudant.com";
  
 if($_GET["URL"]==null)
 {

	//Default URL 
	$url = $BaseURL."/asc";
	
 }
 else
 {
 	$url = $_GET["URL"];
 	
 }
 
  
 if($_GET["ALLDBS"]=="TRUE")
 {
 	$url =  $BaseURL."/_all_dbs"; 
 	
 }
 

//Set up call to the service


$result = file_get_contents($url); 
echo($result);



?>
