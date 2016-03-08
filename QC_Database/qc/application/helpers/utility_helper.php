<?php
function set_percent_cookies(){
	$CI =& get_instance();
	$resources = $CI->config->item('resources');


	//echo "WE ARE READING THE DATA FROM THE XML FILE THIS HAS TO HAPPEN NOT VERY OFTEN";
	#loading the xml for the qc table
	$qc_table_xml=simplexml_load_file($resources."config/qc_table_definition.xml");
	$CI->input->set_cookie("flag_percent_set", "true", 0);

	foreach($qc_table_xml->column as $key=>$val){
		if (strpos($val->type,"DECIMAL") !== false){
			#set_cookies("percent_".$val->field, $val->percentage);
			if ((bool)$val->percentage)
				$CI->input->set_cookie(sha1($val->field), $val->percentage, 0, "","", "percent_");
		}
	}

}



/*
This function is use to get the flag for the fields that have decimal type
This function should be used only when the cookies are not set or they have JUST been set now.
 */
function get_percent_flags(){
	$CI =& get_instance();
	$resources = $CI->config->item('resources');
	$flags = array();
	if (is_percent_flag_set()){
		foreach($_COOKIE as $key=>$val){
			if (strpos($key, "percent_") !== false){
				$flags[$key] = $val;
			}
		}
	}
	else{
		set_percent_cookies();
		$qc_table_xml=simplexml_load_file($resources."config/qc_table_definition.xml");
		foreach($qc_table_xml->column as $key=>$val){
			if (strpos($val->type,"DECIMAL") !== false){
				$flags["percent_".sha1($val->field)] = (string)$val->percentage;
			}
		}
	}
	return $flags;
}


function is_percent_flag_set(){
	$CI =& get_instance();
	return !($CI->input->cookie("flag_percent_set")==false);
}


function get_column_order(){
	$order = array();
	if (is_column_order_flag_set()){
		//echo "getting from cookies";
		#echo "<pre>";
		#print_r($_COOKIE);
		#echo "</pre>";
		foreach($_COOKIE as $key=>$val){
			if (strpos($key, 'col-order-') !== false && $key != "col-order-set"){
				$order[] = (int)$val;
			}
		}
	}
	else{
		//echo "We are getting the default column order";
		//set the cookie with the default and return the default
		$CI =& get_instance();
		$CI->input->set_cookie("0", 0, 0, "","", "col-order-");
		$CI->input->set_cookie("1", 1, 0, "","", "col-order-");
		$CI->input->set_cookie("2", 2, 0, "","", "col-order-");
		$CI->input->set_cookie("set", 1, 0, "","", "col-order-");

		$order[] = 0;
		$order[] = 1;
		$order[] = 2;
	}
	return $order;
}


function is_column_order_flag_set(){
	$CI =& get_instance();
	return !($CI->input->cookie("col-order-set")==false);
}


function set_cookies($name, $value, $seconds="0"){
	return setcookie($name, $value, $seconds);
}





function get_header($inArray){
	$header = array();
	if(is_array($inArray)){
		if(is_array($inArray[0])){
			$inArray = $inArray[0];
		}
		foreach($inArray as $key=>$val){
			$header[] = $key;
		}
	}
	return $header;
}
?>
