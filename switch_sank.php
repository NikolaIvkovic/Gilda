<?php
include 'db.php';
switch ($_REQUEST['action']) {
	case 'autocomplete' :
		$sql = 'SELECT cl_broj, cl_imeprezime FROM clanovi WHERE cl_imeprezime LIKE :searchstr';
		$searchstr = '%'.$_POST['autocomplete'].'%';
		$stmt = $db->prepare($sql);
		$stmt->execute(['searchstr' => $searchstr]);
		$data = array();
		while ($row = $stmt->fetch()) {
			$data[] = $row;
		}

		echo json_encode($data);
	break;
	case 'orderartikal' :
	include 'classes/sank_lista.php';
		$data = array('art_id' => $_REQUEST['art_id'],
						'rd_id' => $_REQUEST['rd_id'],
						'cl_broj' => (isset($_REQUEST['cl_broj'])&& $_REQUEST['cl_broj'] != '') ? $_REQUEST['cl_broj'] : 0);
		
		
		Sanklista::orderArtikal ($data, $db);
	break;
	case 'payartikal':
		include 'classes/sank_lista.php';
		$data = array('sl_id' => $_REQUEST['sl_id']);
		Sanklista::payArtikal($data, $db);
		$cl_broj = (isset($_REQUEST['cl_broj']) && $_REQUEST['cl_broj'] != '') ? $_REQUEST['cl_broj'] : 0;
		$data = array('rd_id' => $_REQUEST['rd_id'],
						'cl_broj' => $cl_broj);
		echo Sanklista::getAccordionClan($data, $db);
	break;
	case 'rebuildaccordion':
		include 'classes/sank_lista.php';
		$data = array('rd_id' => $_REQUEST['rd_id']);
		echo json_encode(Sanklista::rebuildAccordion($data, $db));
	break;
	case 'getaccordionclan' :
		include 'classes/sank_lista.php';
		$data = array ('cl_broj' => ($_REQUEST['cl_broj'] == 'NaN') ? 0 : $_REQUEST['cl_broj'], 
						'rd_id' => $_REQUEST['rd_id']);
		echo Sanklista::getAccordionClan($data, $db);
	break;
	case 'newradnidan':
		include 'classes/sank_lista.php';
		echo Sanklista::newRadniDan($db);
	break;
	case 'endradnidan': 
		include 'classes/sank_lista.php';
		include 'backup.php';
		$data = array('rd_id' => $_REQUEST['rd_id']);
		Sanklista::endRadniDan($data, $db);
	break;

}
?>