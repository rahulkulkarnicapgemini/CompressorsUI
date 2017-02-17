<?php
  //parse vcap
  if( getenv("VCAP_SERVICES") ) {
      $json = getenv("VCAP_SERVICES");
  } 
  # No DB credentials
  else {
      throw new Exception("No Database Information Available.", 1);
  }

?>
