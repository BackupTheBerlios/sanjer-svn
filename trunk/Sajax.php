<?php	

class SAJAX{

	/*
	 * GLOBALS AND DEFAULTS
	 *
	 */
	var $sajax_debug_mode;
	var $sajax_export_list;
	var $sajax_request_type;
	var $sajax_remote_uri;
	var $sajax_js_has_been_shown;

  function sajax($requestType = "GET", $remoteUri = ""){
    $this->sajax_debug_mode = 0;
	$this->sajax_export_list = array();
	$this->sajax_request_type = $requestType;
	
	if($remoteUri == "") $remoteUri = basename($_SERVER["PHP_SELF"]);
	$this->sajax_remote_uri = $remoteUri;
	
	$this->sajax_js_has_been_shown = 0;
//    $this->this->sajax_remote_uri = $this->sajax_get_my_uri();
  }

  function sajax_init() {
  }

/*
  function sajax_get_my_uri() {
	global $REQUEST_URI;

	return $REQUEST_URI;
  }
*/
  function sajax_handle_client_request() {

	$mode = "";

	if (! empty($_GET["rs"]))
		$mode = "get";

	if (!empty($_POST["rs"]))
		$mode = "post";

	if (empty($mode))
		return;

	if ($mode == "get") {
		// Bust cache in the head
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// always modified
		header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
		header ("Pragma: no-cache");                          // HTTP/1.0
		$func_name = $_GET["rs"];
		if (! empty($_GET["rsargs"]))
			$args = $_GET["rsargs"];
		else
			$args = array();
	}
	else {
		$func_name = $_POST["rs"];
		if (! empty($_POST["rsargs"]))
			$args = $_POST["rsargs"];
		else
			$args = array();
	}

	if (! in_array($func_name, $this->sajax_export_list))
		echo "-:$func_name not callable";
	else {
		echo "+:";
		$result = call_user_func_array($func_name, $args);
		echo $result;
	}
	exit;
  }


  function sajax_get_common_js() {

	$t = strtoupper($this->sajax_request_type);
	if ($t != "GET" && $t != "POST")
		return "// Invalid type: $t.. \n\n";

	ob_start();
	?>

	// remote scripting library
	// (c) copyright 2005 modernmethod, inc
	var sajax_debug_mode = <?php echo $this->sajax_debug_mode ? "true" : "false"; ?>;
	var sajax_request_type = "<?php echo $t; ?>";

	function sajax_debug(text) {
		if (sajax_debug_mode)
			alert("RSD: " + text)
	}
	function sajax_init_object() {
		sajax_debug("sajax_init_object() called..");

		var A;
		try {
			A=new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				A=new ActiveXObject("Microsoft.XMLHTTP");
			} catch (oc) {
				A=null;
			}
		}

		if(!A && typeof XMLHttpRequest != "undefined")
			A = new XMLHttpRequest();
		if (!A)
			sajax_debug("Could not create connection object.");
		return A;
	}
	function sajax_do_call(func_name, args) {
		var i, x, n;
		var uri;
		var post_data;

		uri = "<?php echo $this->sajax_remote_uri; ?>";
		if (sajax_request_type == "GET") {
			if (uri.indexOf("?") == -1)
				uri = uri + "?rs=" + escape(func_name);
			else
				uri = uri + "&rs=" + escape(func_name);
			for (i = 0; i < args.length-1; i++)
				uri = uri + "&rsargs[]=" + escape(args[i]);
			uri = uri + "&rsrnd=" + new Date().getTime();
			post_data = null;
		} else {
			post_data = "rs=" + escape(func_name);
			for (i = 0; i < args.length-1; i++)
				post_data = post_data + "&rsargs[]=" + escape(args[i]);
		}

		x = sajax_init_object();
        try{
			x.open(sajax_request_type, uri, true);
		} catch (e){
            sajax_debug("problem opening XMLHttpRequest:" + e.description + "\n" +
            "[sajax_request_type, uri] = [" + sajax_request_type + ", " + uri +"]");
        }
		if (sajax_request_type == "POST") {
			x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
			x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		}
		x.onreadystatechange = function() {
			if (x.readyState != 4)
				return;
			sajax_debug("received " + x.responseText);

			var status;
			var data;
			status = x.responseText.charAt(0);
			data = x.responseText.substring(2);
			if (status == "-")
				alert("Error: " + data);
			else
				args[args.length-1](data);
		}
		x.send(post_data);
		sajax_debug(func_name + " uri = " + uri + "/post = " + post_data);
		sajax_debug(func_name + " waiting..");
		delete x;
	}

	<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
  }

  function sajax_show_common_js() {
    echo sajax_get_common_js();
  }

  // javascript escape a value
  function sajax_esc($val){
	return str_replace('"', '\\\\"', $val);
  }


  function sajax_get_one_stub($func_name) {
	ob_start();
	?>

	// wrapper for <?php echo $func_name; ?>

	function x_<?php echo $func_name; ?>() {
		sajax_do_call("<?php echo $func_name; ?>",
			x_<?php echo $func_name; ?>.arguments);
	}

	<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
  }

  function sajax_show_one_stub($func_name) {
	echo $this->sajax_get_one_stub($func_name);
  }

  function sajax_export() {
	$n = func_num_args();
	for ($i = 0; $i < $n; $i++) {
		$this->sajax_export_list[] = func_get_arg($i);
	}
  }
  
  
  function sajax_get_javascript(){

	$html = "";
	if (! $this->sajax_js_has_been_shown) {
		$html .= $this->sajax_get_common_js();
		$this->sajax_js_has_been_shown = 1;
	}
	foreach ($this->sajax_export_list as $func) {
		$html .= $this->sajax_get_one_stub($func);
	}
	return $html;
  }
  
  function sajax_show_javascript(){
	echo $this->sajax_get_javascript();
  }
  
}

?>
