<?php
session_start();
DEFINE ('APP_DIR', $_SERVER['DOCUMENT_ROOT'].'/sank/');
DEFINE ('APP_ROOT', $_SERVER['HTTP_HOST'].'/sank/');
require 'db.php';
require 'classes/error_handler.php';
$errors = errorHandler::load();
$errors->listErrors();
?>