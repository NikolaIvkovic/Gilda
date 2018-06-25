<?php
include 'autoloader.php';

echo Classes\Rankings::getRankings($_REQUEST['gm_id']);
?>