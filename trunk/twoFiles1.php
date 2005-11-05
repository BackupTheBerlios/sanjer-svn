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
// Notice "twoFiles2.php", which is where the server side functions are.
    $sanjer = new SANJER("POST", $functionArr, "twoFiles2.php");

// Notice that in the two file version, the call to start_listening()
// is required only in the file that ocntains the server side functions.
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=WINDOWS-1255">
	<title>Sajax + JSON test</title>
    <LINK REL=StyleSheet HREF="sanjer.css" TYPE="text/css" MEDIA=screen> 
	<?
// This function will create all the JS you need to get it to work.
	$sanjer->show_javascript();
	?>
	<script type="text/javascript">
    
	function justAddLineBreaks(text){
        return text.replace(/(\n)/g, "<br />$1");
    }
	
	function call_remote_php_cb(result) {
    // Again, it's so easy to put the result into a JS object (thanks to JSON)
        var dataReceived = sanjer.json2object(result);
        dataReceived.text = decodeURI(dataReceived.text);
        document.getElementById("result").innerHTML = justAddLineBreaks(dataReceived.text);
        count = dataReceived.count;
        document.getElementById("count").innerHTML = count; 
	}
	
    var count = 0;
    
	function call_remote_php() {
		var dataToSend = {"text": "", "count": "0"};
        dataToSend.count = count;
		dataToSend.text = encodeURI(document.getElementById("callRemote").value);
		try{
            //the data sent to the server function is encoded into JSON
            //SANJER's call_function() is used to call the backend function
            sanjer.call_function("boomerang", sanjer.object2json(dataToSend), call_remote_php_cb);
		} catch (e) {
            alert(e.message);
        }
	}
	
	</script>
	
</head>
<body dir="ltr">
<H2>SANJER Example: Using 2 files</H2>
<H3>(One with the JS and HTML, one only with PHP functions)</H3>
In this example, one file contains the JS and HTML required, and another contains the PHP functions<BR /><BR /> 
Write something in the box and press the "Send" button.<BR />
It will be ping ponged with the server via Ajax, returned and displayed below the box. <BR />
Just for the fun of it there's also a counter to see how many time you use the send button. <BR />
The counter is sent as well and incremented in the server.<BR /> 
<form>
	<textarea name="callRemote" id="callRemote" value="" rows="10" cols="10"></textarea>
	<input type="button" name="check" value="Send"
		onclick="call_remote_php(); return false;">
 </form>
 You wrote:
 <span id="result"></span>
 <BR />
 Count is:
 <span id="count">0</span>
 
 <p><a href="<?= $_SERVER['PHP_SELF'] ?>?show_source">See the PHP source of this file</a></p>
 <p><a href="twoFiles2.php?show_source">See the PHP source of the file containing the PHP function</a></p>
</body>
</html>
