<?php
	include_once 'DbHandler.php';
	$db = new DbHandler(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);
	$result = $db->connect();
	if(!$result){
	    die();
	}
?>