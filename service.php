<?phpo

//Get the connection Information 
if( getenv( "VCAP_SERVICES" ) )
{
    $details  = json_decode( getenv( "VCAP_SERVICES" ), true );
    $dsn      = $details [ "dashDB" ][0][ "credentials" ][ "dsn" ];
    $ssl_dsn  = $details [ "dashDB" ][0][ "credentials" ][ "ssldsn" ];
    
   $driver = "DRIVER={IBM DB2 ODBC DRIVER};";
   $conn_string = $driver . $dsn; 
  
  
  //Print the connection string
  echo($conn_string."<br />");   
  
  //Connect to the database
  $conn = db2_connect( $conn_string, "", "" );
  
  if($conn)
  {
    echo("Connection Succeeded");
    db2_close($conn);
  }
  elwse
  {
    echo("Connection Failed"); 
  }
}
else
{
    echo("NO Credentials");
}



?>
