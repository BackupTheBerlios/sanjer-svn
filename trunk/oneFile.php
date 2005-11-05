<?php

	if (isset($_REQUEST['show_source'])) {
		highlight_file(__FILE__);
		exit;
	}
	
// That's all you need to include, both for the PHP and the JS
	require_once("sanjer.php");
	
// You can pass the PHP functions that AJAX can call
// as an array to the constructor.
// You can also do it later, using the register() function.
    $functionArr = array ("boomerang");

// create your SANJER PHP object.
    $sanjer = new SANJER("POST", $functionArr);

// Set the Debug if needed.
//    $sanjer->set_debug_mode(true);

// Tell the SANJER object to listen to calls from the JS.
    $sanjer->start_listening();


// Now let's set the function that the JS will call.
// Notice that one global SANJER object is all you need.
// And see how easy it is to put it into a PHP object and back.
	function boomerang($data) {
      global $sanjer;    
      $subs = $sanjer->json2object($data);
      $subs->count++;
      return $sanjer->object2json($subs);
	}	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=WINDOWS-1255">
	<title>SANJER One File Example</title>
    <LINK REL=StyleSheet HREF="sanjer.css" TYPE="text/css" MEDIA=screen> 

	<?php
// This function will create all the JS you need to get it to work.
	$sanjer->show_javascript();
	?>
	<script type="text/javascript">
    
	function justAddLineBreaks(text){
        return text.replace(/(\n)/g, "<br />$1");
    }

    // The cllback function that accepts the results from the server
	function call_php_cb(result) {
      // Again, it's so easy to put the result into a JS object (thanks to JSON)
        var dataReceived = sanjer.json2object(result);
        dataReceived.text = decodeURI(dataReceived.text);
        document.getElementById("result").innerHTML = justAddLineBreaks(dataReceived.text);
        count = dataReceived.count;
        document.getElementById("count").innerHTML = count; 
	}
	
    var count = 0;
    
	function call_php() {
		var dataToSend = {"text": "", "count": "0"};
        dataToSend.count = count;
		dataToSend.text = encodeURI(document.getElementById("callRemote").value);
		try{
            //the data sent to the server function is encoded into JSON 
            //SANJER's call_function() is used to call the backend function
            sanjer.call_function("boomerang", sanjer.object2json(dataToSend), call_php_cb);
		} catch (e) {
            alert(e.message);
        }
	}
	
	</script>
	
</head>
<body dir="ltr">
<H2>SANJER Example:</H2>
<H3>Using 1 file (with the JS, HTML, and PHP)</H3>
In this example, one file contains all the JS, HMTL and PHP code required. <BR /><BR />
Write something in the box and press the "Send" button.<BR />
It will be ping ponged with the server via Ajax, returned and displayed below the box. <BR />
Just for the fun of it there's also a counter to see how many time you use the send button. <BR />
The counter is sent as well and incremented in the server.<BR /> 
   

<form>
	<textarea name="callRemote" id="callRemote" value="" rows="10" cols="10"></textarea>
	<input type="button" name="check" value="Send"
		onclick="call_php(); return false;">
 </form>
 You wrote:
 <span id="result"></span>
 <BR />
 Count is:
 <span id="count">0</span>
 
 <p><a href="<?= $_SERVER['PHP_SELF'] ?>?show_source">See the PHP source of this file</a></p>
</body>
</html>
