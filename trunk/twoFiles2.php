<?php

// This file contains only the server side functions and the SANJER

  require_once("sanjer.php");

  $funcArr = array("boomerang");

  $sanjer = new SANJER("POST", $funcArr);

// Tell SANJER to wait for calls from the client side.
  $sanjer->start_listening();
  

// The function that the JS will call.
// Notice that one global SANJER object is all you need.
// And see how easy it is to put it into a PHP object and back.
	function boomerang($data) {
      global $sanjer;    
      $subs = $sanjer->json2object($data);
      $subs->count++;
      return $sanjer->object2json($subs);
	}
	
?>
