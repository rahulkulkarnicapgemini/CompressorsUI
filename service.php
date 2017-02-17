<?php
  //parse vcap
  if( getenv("VCAP_SERVICES") ) {
      $json = getenv("VCAP_SERVICES");
  } 
  # No DB credentials
  else {
      throw new Exception("No Database Information Available.", 1);
  }
# Decode JSON and gather DB Info
$services_json = json_decode($json,true);
$bludb_config = $services_json["dashDB"][0]["credentials"];



// create DB connect string
$conn_string = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".
   $bludb_config["db"].
   ";HOSTNAME=".
   $bludb_config["host"].
   ";PORT=".
   $bludb_config["port"].
   ";PROTOCOL=TCPIP;UID=".
   $bludb_config["username"].
   ";PWD=".
   $bludb_config["password"].
   ";";


echo($conn_string);


?>

//echo($conn_string);


