<?php
require 'db.php';
require 'autoloader.php';
switch ($_REQUEST['action']) {
	case 'art_new' :
		$mode = 'add';
		include 'includes/artikal_izmena.php';
	break;
	case 'art_update':
		$mode = 'update';
		$art_id = $_REQUEST['art_id'];
		include 'includes/artikal_izmena.php';
	break;
	case 'art_list':
		$kat_id = $_REQUEST['kat_id'];
		include 'includes/artikal_lista.php';
	break;
	case 'cl_list':
		include 'includes/clan_lista.php';
	break;
	case 'cl_new':
		$mode = 'add';
		include 'includes/clan_izmena.php';
	break;
	case 'cl_update':
		$mode = 'update';
		$cl_broj = $_REQUEST['cl_broj'];
		include 'includes/clan_izmena.php';
	break;
	case 'liste':
		include 'includes/sanklista.php';
	break;
	case 'listeMesec': 
		$data = array('month' => $_REQUEST['month']);
		echo Classes\Sanklista::getListTable($data, $db);
	break;
	case 'dnevnalista':
		$data = array('rd_id' => $_REQUEST['rd_id']);
		$sank = Classes\Sanklista::getSankLista($data, $db);
		echo $sank['placeno'].$sank['neplaceno'].$sank['napomene'];
	break;

	case 'newnapomena':
		$data = array('rd_id' => $_REQUEST['rd_id'],
						'np_sadrzaj' => nl2br($_REQUEST['np_sadrzaj']));
		Classes\Sanklista::newNapomena($data, $db);
	break;
	case 'duznici':
		if (isset($_COOKIE['rd_id'])){
			$data = array('rd_id' => $_COOKIE['rd_id']);
			echo Classes\Sanklista::getDuznici($data, $db);
		}
		else {
			echo Classes\Sanklista::getDuznici(null, $db);
		}
	break;
	case 'getDuznikArtikli' :
		$data = array('cl_broj' => $_REQUEST['cl_broj']);
		echo Classes\Sanklista::getDuznikArtikli($data, $db);
	break;
}
?>