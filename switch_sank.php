<?php
require 'db.php';
require 'autoloader.php';
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
		$data = array('art_id' => $_REQUEST['art_id'],
						'rd_id' => $_REQUEST['rd_id'],
						'cl_broj' => (isset($_REQUEST['cl_broj'])&& $_REQUEST['cl_broj'] != '') ? $_REQUEST['cl_broj'] : 0);
		
		
		Classes\Sanklista::orderArtikal ($data, $db);
	break;
	case 'payartikal':
		$data = array('sl_id' => $_REQUEST['sl_id']);
		Classes\Sanklista::payArtikal($data, $db);
		$cl_broj = (isset($_REQUEST['cl_broj']) && $_REQUEST['cl_broj'] != '') ? $_REQUEST['cl_broj'] : 0;
		$data = array('rd_id' => $_REQUEST['rd_id'],
						'cl_broj' => $cl_broj);
		echo Classes\Sanklista::getAccordionClan($data, $db);
	break;
	case 'rebuildaccordion':
		$data = array('rd_id' => $_REQUEST['rd_id']);
		echo json_encode(Classes\Sanklista::rebuildAccordion($data, $db));
	break;
	case 'getaccordionclan' :
		$data = array ('cl_broj' => ($_REQUEST['cl_broj'] == 'NaN') ? 0 : $_REQUEST['cl_broj'], 
						'rd_id' => $_REQUEST['rd_id']);
		echo Classes\Sanklista::getAccordionClan($data, $db);
	break;
	case 'newradnidan':
		echo Classes\Sanklista::newRadniDan($db);
	break;
	case 'endradnidan': 
		//include 'backup.php';
		$data = array('rd_id' => $_REQUEST['rd_id']);
		Classes\Sanklista::endRadniDan($data, $db);
	break;

}
?>