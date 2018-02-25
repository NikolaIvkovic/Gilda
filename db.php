<?php
require 'db_host.php';
$opt = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
try{
$db = new PDO ('mysql:host='.DBHOST.';dbname='.DBNAME.';charset=utf8', USER, PASS, $opt);
}
catch (Exception $e) {	
	$_SESSION['errors']['system'][] = $e;
}
?>