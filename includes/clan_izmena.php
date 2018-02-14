<?php
require 'header.php';
include APP_DIR.'classes/clanovi.php';
if ($mode == 'update') {
	$action = 'IZMENI';
	$clan = new Clan($cl_broj, $db);
	$broj = '<input type="hidden" id="cl_broj" name="cl_broj" value="'.$cl_broj.'">'.$cl_broj;
}
if ($mode == 'add') {
	$action = 'DODAJ';
	$broj = '<input type="text" name="cl_broj" id="cl_broj" size ="4" value="'.Clan::nextBroj($db).'">';
}
$values = array();
if (isset($_SESSION['formdata']) && count($_SESSION['formdata']) > 0) {
	$values['broj'] = $_SESSION['formdata']['cl_broj'];
	$values['imeprezime'] = $_SESSION['formdata']['cl_imeprezime'];
	$values['rodjen'] = $_SESSION['formdata']['cl_rodjen'];
	$values['telefon'] = $_SESSION['formdata']['cl_telefon'];
	$values['email'] = $_SESSION['formdata']['cl_email'];
	$values['facebook'] = $_SESSION['formdata']['cl_facebook'];
	$values['igre'] = $_SESSION['formdata']['cl_igre'];

	unset($_SESSION['formdata']);
}
else {
	$values['broj'] = (isset($cl_broj)) ? $cl_broj : Clan::nextBroj($db);
	$values['imeprezime'] = (isset($clan)) ? $clan->getImePrezime() : '';
	$values['rodjen'] = (isset($clan)) ? Clan::dateDb2Form($clan->getRodjen()) : '';
	$values['telefon'] = (isset($clan)) ? $clan->getTelefon() : '';
	$values['email'] = (isset($clan)) ? $clan->getEmail() : '';
	$values['facebook'] = (isset($clan)) ? $clan->getfacebook() : '';
	$values['igre'] = (isset($clan)) ? $clan->getIgre() : '';
}

?>
	<div class="formwrapper">
	<div class="naslov"><?= $action?> ČLANA</div>
		<form action ="transact_admin.php" method="post" enctype="multipart/form-data">
			<table class="formtable">
				<tr>
					<td>Članski broj</td>
					<td><?=$broj;?></td>
				</tr>
				<tr>
					<td>Ime i prezime</td>
					<td><input type="text" name="cl_imeprezime" id="cl_imeprezime"  value="<?=$values['imeprezime'] ;?>"></td>
				</tr>
				<tr>
					<td>Datum rođenja</td>
					<td><input type="text" name="cl_rodjen" id="cl_rodjen" size="8" value="<?=$values['rodjen'] ;?>"></td>
				</tr>
				<tr>
					<td>Broj telefona</td>
					<td><input type="text" name="cl_telefon" id="cl_telefon" size="12" value="<?=$values['telefon'] ;?>"></td>
				</tr>
				<tr>
					<td>E-mail</td>
					<td><input type="text" name="cl_email" id="cl_email"  value="<?=$values['email'] ;?>"></td>
				</tr>
				
				<tr>
					<td>Facebook</td>
					<td><input type="text" name="cl_facebook" id="cl_facebook"  value="<?=$values['facebook'] ;?>"></td>
				</tr>
				<tr>
					<td>Omiljene igre</td>
					<td><textarea name="cl_igre" id="cl_igre" cols="20" rows="4"><?=$values['igre'] ;?></textarea></td>
				</tr>
			<tr >
			<td colspan="2" class="submitCell"><input class="formButton" type="submit" name="submit" id="submit" value ="<?= $action; ?> ČLANA">
			</td>
			</tr>
			</table>
		</form>
	</div>