<?php
require 'header.php';
switch ($_POST['submit']) {
	case 'IZMENI ARTIKAL': 
		$val = new Classes\Validator();
		$val->addfield ($_POST['art_naziv'], 'Naziv artikla', 'string');
		$val->addfield ($_POST['art_prodajna'], 'Prodajna cena', 'num');
		$val->addfield ($_POST['art_stanje'], 'Stanje na lageru', 'num');

		if ($val->validate()) {
			$data = array();
			$data['art_naziv'] = $_POST['art_naziv'];
			$data['art_prodajna'] = $_POST['art_prodajna'];
			$data['art_stanje'] = $_POST['art_stanje'];
			$data['kat_id'] = $_POST['kat_id'];
			$data['art_alkoholno'] = ($_POST['art_alkoholno'] == 'on') ? 1 : 0;
			$data['art_id'] = $_POST['art_id'];
			
			if (Classes\Artikal::updateArtikal($data, $db) == true) {
				$_SESSION['errors']['notices'][] = 'Artikal uspešno ažuriran!';
				header ('Location: index_admin.php');
			}
			else {
				setcookie('lastActive', 'artUpdate');
				setcookie('artUpdateId', $_POST['art_id']);
				header ('Location: index_admin.php');
			}
		}
		else {
			setcookie('lastActive', 'artUpdate');
			setcookie('artUpdateId', $_POST['art_id']);
			header ('Location: index_admin.php');
		}
	break;
	case 'DODAJ ARTIKAL' :
		$val = new Classes\Validator();
		$val->addfield ($_POST['art_naziv'], 'Naziv artikla', 'string');
		$val->addfield ($_POST['art_prodajna'], 'Prodajna cena', 'num');
		$val->addfield ($_POST['art_stanje'], 'Stanje na lageru', 'num');
		if ($val->validate()) {
			$imgsrc = '';
			if (isset($_FILES['art_slika'])) {
				$slika = new Classes\Slika ($_FILES['art_slika']);
				$slika->saveResized();
				$imgsrc = $slika->getFilename();
			}
			$data = array();
			$data['art_naziv'] = $_POST['art_naziv'];
			$data['art_prodajna'] = $_POST['art_prodajna'];
			$data['art_stanje'] = $_POST['art_stanje'];
			$data['kat_id'] = $_POST['kat_id'];
			$data['art_alkoholno'] = ($_POST['art_alkoholno'] == 'on') ? 1 : 0;
			$data['art_slika'] = $imgsrc;
			if (Classes\Artikal::newArtikal($data, $db) == true) {
				$_SESSION['errors']['notices'][] = 'Artikal uspešno dodat!';
				header ('Location: index_admin.php');
			}
		}
		else {
			$_SESSION['formdata'] = array();
			$_SESSION['formdata']['art_naziv'] = $_POST['art_naziv'];
			$_SESSION['formdata']['art_prodajna'] = $_POST['art_prodajna'];
			$_SESSION['formdata']['art_stanje'] = $_POST['art_stanje'];
			$_SESSION['formdata']['kat_id'] = $_POST['kat_id'];
			$_SESSION['formdata']['art_alkoholno'] = $_POST['art_alkoholno'];
			setcookie('lastActive', 'artNew');
			header ('Location: index_admin.php');
		}
	break;
	
		case 'IZMENI ČLANA': 
		$val = new Classes\Validator();
		$val->addfield ($_POST['cl_imeprezime'], 'Ime i prezime', 'string');
		$val->addfield ($_POST['cl_rodjen'], 'Datum rođenja', 'req');
		if ($val->validate() && Classes\Clan::dateForm2Db($_POST['cl_rodjen']) != false){
			$data = array();
			$data['cl_imeprezime'] = $_POST['cl_imeprezime'];
			$data['cl_rodjen'] = Classes\Clan::dateForm2Db($_POST['cl_rodjen']);
			$data['cl_telefon'] = $_POST['cl_telefon'];
			$data['cl_email'] = $_POST['cl_email'];
			$data['cl_facebook'] = $_POST['cl_facebook'];
			$data['cl_igre'] = $_POST['cl_igre'];
			$data['cl_broj'] = $_POST['cl_broj'];
			
			if (Classes\Clan::updateClan($data, $db) == true) {
				$_SESSION['errors']['notices'][] = 'Član uspešno ažuriran!';
				header ('Location: index_admin.php');
			}
			else {

				setcookie('lastActive', 'clUpdate');
				setcookie('clUpdateBroj', $_POST['cl_broj']);
				header ('Location: index_admin.php');
			}
		}
		else {
			setcookie('lastActive', 'clUpdate');
			setcookie('clUpdateBroj', $_POST['cl_broj']);
			header ('Location: index_admin.php');
		}
	break;
	
	case 'DODAJ ČLANA':
		$val = new Classes\Validator();
		$val->addfield ($_POST['cl_imeprezime'], 'Ime i prezime', 'string');
		$val->addfield ($_POST['cl_rodjen'], 'Datum rođenja', 'req');
		if ($val->validate() && Classes\Clan::dateForm2Db($_POST['cl_rodjen']) != false){
			$data = array();
			$data['cl_broj'] = $_POST['cl_broj'];
			$data['cl_imeprezime'] = $_POST['cl_imeprezime'];
			$data['cl_rodjen'] = Classes\Clan::dateForm2Db($_POST['cl_rodjen']);
			$data['cl_telefon'] = $_POST['cl_telefon'];
			$data['cl_email'] = $_POST['cl_email'];
			$data['cl_facebook'] = $_POST['cl_facebook'];
			$data['cl_igre'] = $_POST['cl_igre'];
			if (Classes\Clan::newClan($data, $db)) {
				$_SESSION['errors']['notices'][] = 'Član uspešno dodat!';
				header ('Location: index_admin.php');
			}
		}
		else {
			$_SESSION['formdata'] = array();
			$_SESSION['formdata']['cl_broj'] = $_POST['cl_broj'];
			$_SESSION['formdata']['cl_imeprezime'] = $_POST['cl_imeprezime'];
			$_SESSION['formdata']['cl_rodjen'] = $_POST['cl_rodjen'];
			$_SESSION['formdata']['cl_telefon'] = $_POST['cl_telefon'];
			$_SESSION['formdata']['cl_email'] = $_POST['cl_email'];
			$_SESSION['formdata']['cl_facebook'] = $_POST['cl_facebook'];
			$_SESSION['formdata']['cl_igre'] = $_POST['cl_igre'];
			setcookie('lastActive', 'clNew');
			header ('Location: index_admin.php');
			
		}
	break;
	case ('AŽURIRAJ STANJE') :
		foreach ($_POST['fields'] as $data) {
			Classes\Artikal::updateStanje($data, $db);
		}
		echo 'Stanje na lageru uspešno ažurirano!';
	break;
	
	case ('deleteArtikal') :
		Classes\Artikal::deleteArtikal ($_POST['art_id'], $db);
	break;
	
}
?>