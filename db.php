<?php
$opt = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
try{
$db = new PDO ('mysql:host=localhost;dbname=gildadb;charset=utf8', 'root', '', $opt);
}
catch (Exception $e) {	
	$_SESSION['errors']['system'][] = $e;
}
?>