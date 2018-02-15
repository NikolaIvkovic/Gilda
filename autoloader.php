<?php
spl_autoload_register (
	function($class) {
		$class = str_replace('\\', '/', $class);
		include $_SERVER['DOCUMENT_ROOT'].'/sank/'.$class.'.php';
	}
);
?>