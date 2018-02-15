<?php
session_start();
DEFINE ('APP_DIR', $_SERVER['DOCUMENT_ROOT'].'/sank/');
require 'db.php';
require 'autoloader.php';
$errors = Classes\ErrorHandler::load();
$errors->listErrors();
?>