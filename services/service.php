

<?php 

//Header for access
 header('Access-Control-Allow-Origin: *');
 
$BaseURL = "https://a6a59714-f2ea-4b8d-bc41-e15f94fe5252-bluemix.cloudant.com"; //Database subscription.



$DefaultDB = "iotp_if34ew_compressors_2017-02";//Please implement logic to change this to current month.



  
 if($_GET["URL"]==null)
 {

	//Default URL 
	$url = $BaseURL."/".$DefaultDB; //Default Database
	
 }
 else
 {
 	$url = $_GET["URL"];
 	
 }
 

  
 if($_GET["ALLDOCS"]=="TRUE")
 {
 	$url =  $url ."/_all_docs"; 
 	
 }

//Set up call to the service
$result = file_get_contents($url); 
echo($result);
?>
