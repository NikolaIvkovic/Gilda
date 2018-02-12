<?php
include 'db.php';
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
		include 'classes/sank_lista.php';
		$data = array('month' => $_REQUEST['month']);
		echo Sanklista::getListTable($data, $db);
	break;
	case 'dnevnalista':
		include 'classes/sank_lista.php';
		$data = array('rd_id' => $_REQUEST['rd_id']);
		$sank = Sanklista::getSankLista($data, $db);
		echo $sank['placeno'].$sank['neplaceno'].$sank['napomene'];
	break;
	case 'nabavka':
		include 'classes/artikli.php';
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$headers .= "From: gildaigraca@zoho.eu\r\n";
		$headers .= "Reply-To: gildaigraca@zoho.eu\r\n";
		$headers .= "Return-Path: gildaigraca@zoho.eu\r\n";
		$table = Artikal::nabavka($db);
		$subject = 'Nabavka za Gildu - '.date('d.m.Y');
		mail("jelovacn@gmail.com",$subject,$table, $headers);
		echo $table;
	break;
	case 'newnapomena':
		include 'classes/sank_lista.php';
		$data = array('rd_id' => $_REQUEST['rd_id'],
						'np_sadrzaj' => $_REQUEST['np_sadrzaj']);
		Sanklista::newNapomena($data, $db);
	break;
	case 'duznici':
		include 'classes/sank_lista.php';
		if (isset($_COOKIE['rd_id'])){
			$data = array('rd_id' => $_COOKIE['rd_id']);
			echo Sanklista::getDuznici($data, $db);
		}
		else {
			echo Sanklista::getDuznici(null, $db);
		}
	break;
	case 'getDuznikArtikli' :
		include 'classes/sank_lista.php';
		$data = array('cl_broj' => $_REQUEST['cl_broj']);
		echo Sanklista::getDuznikArtikli($data, $db);
	break;
}
?>