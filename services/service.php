

<?php 

//Header for access
 header('Access-Control-Allow-Origin: *');
 
//echo("Connecting to a database"); 


$database = "BLUDB";        # Get these database details from
$hostname = "dashdb-entry-yp-dal09-07.services.dal.bluemix.net";  # the Connect page of the dashDB
$user     = "dash10552";   # web console.
$password = "7172e8f84cca";   #
$port     = 50000;          #
$ssl_port = 50001;          #
    
# Build the connection string
#
$driver  = "DRIVER={IBM DB2 ODBC DRIVER};";
$dsn     = "DATABASE=$database; " .
           "HOSTNAME=$hostname;" .
           "PORT=$port; " .
           "PROTOCOL=TCPIP; " .
           "UID=$user;" .
           "PWD=$password;";
$ssl_dsn = "DATABASE=$database; " .
           "HOSTNAME=$hostname;" .
           "PORT=$ssl_port; " .
           "PROTOCOL=TCPIP; " .
           "UID=$user;" .
           "PWD=$password;" .
           "SECURITY=SSL;";
$conn_string = $driver . $dsn;     # Non-SSL
//$conn_string = $driver . $ssl_dsn; # SSL

# Connect
#

$conn = odbc_connect( $conn_string, "", "");


if($conn==null)
{
 echo("No Connection.");
}


if($conn)
{
    echo "Connection succeeded.";

    # Disconnect
    #
    odbc_close( $conn );
}
else
{
    echo "Connection failed.";
}
?>
