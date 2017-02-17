<?php

if(getenv( "VCAP_SERVICES"))
{    
  /*
  $details  = json_decode( getenv( "VCAP_SERVICES" ), true );   
   $dsn      = $details [ "dashDB" ][0][ "credentials" ][ "dsn" ];    
  $ssl_dsn  = $details [ "dashDB" ][0][ "credentials" ][ "ssldsn" ];       
  $driver = "DRIVER={IBM DB2 ODBC DRIVER};";   
  $conn_string = $driver . $dsn; 
  echo($conn_string); */
}
else
{
  echo("No Credentials");
}
?>
