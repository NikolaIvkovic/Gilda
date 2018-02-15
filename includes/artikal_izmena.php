<?php
require 'header.php';
if ($mode == 'update') {
	$action = 'IZMENI';
	$artikal = new Classes\Artikal ($art_id, $db);
	$slika = '';
	$art_id_hidden = '<input type="hidden" name="art_id" value="'.$art_id.'">';
}
if ($mode == 'add') {
	$action = 'DODAJ';
	$art_id_hidden = '';
	$slika ='<tr>
					<td>Slika</td>
					<td><input type="file" name="art_slika" id="art_slika">
					</td>
				</tr>';
}
$values = array();
if (isset($_SESSION['formdata']) && count($_SESSION['formdata']) > 0) {
	$values['naziv'] = $_SESSION['formdata']['art_naziv'];
	$values['prodajna'] = $_SESSION['formdata']['art_prodajna'];
	$values['stanje'] = $_SESSION['formdata']['art_stanje'];
	$values['kat_id'] = $_SESSION['formdata']['kat_id'];
	$values['alkoholno'] = $_SESSION['formdata']['art_alkoholno'];
	unset($_SESSION['formdata']);
}
else {
	$values['naziv'] = (isset($artikal)) ? $artikal->getNaziv() : '';
	$values['prodajna'] = (isset($artikal)) ? $artikal->getProdajna() : '';
	$values['stanje'] = (isset($artikal)) ? $artikal->getStanje() : '';
	$values['kat_id'] = (isset($artikal)) ? $artikal->getKatId() : '';
	$values['alkoholno'] = (isset($artikal)) ? $artikal->getAlkoholno() : '';
	$values['slika'] = (isset($artikal)) ? $artikal->getSlika() : '';
}

?>
	<div class="formwrapper">
	<div class="naslov"><?= $action?> ARTIKAL</div>
		<form action ="transact_admin.php" method="post" enctype="multipart/form-data">
			<table class="formtable">
				<tr>
					<td>Naziv</td>
					<td><input type="text" name="art_naziv" id="art_naziv" value="<?=$values['naziv'] ;?>"></td>
				</tr>

				<tr>
					<td>Prodajna cena</td>
					<td><input type="text" name="art_prodajna" id="art_prodajna" size="5" value="<?=$values['prodajna'] ;?>"></td>
				</tr>
				<tr>
					<td>Stanje</td>
					<td><input type="text" name="art_stanje" id="art_stanje" size="5" value="<?=$values['stanje'] ;?>"></td>
				</tr>
				<tr>
					<td>Kategorija</td>
					<td><select name="kat_id" id="kat_id">
					<?php
					$kat_id = $values['kat_id'];
					$options = Classes\Kategorija::getKategorije($db);
					
					foreach($options as $opt) {
						if ($opt['kat_id'] == $kat_id) {
							echo '<option value="'.$opt['kat_id'].'" selected="selected">'.$opt['kat_naziv'].'</option>';
						}
						else {
							echo '<option value="'.$opt['kat_id'].'">'.$opt['kat_naziv'].'</option>';
						}
					}
					?>
					</select></td>
				</tr>
				<tr>
					<td>Alkoholno</td>
					<td>
					<?php
					$checked = ($values['alkoholno'] == 1) ? 'checked = "checked"' : '';
					?>
					<input type="checkbox" name="art_alkoholno" id="art_alkoholno" <?= $checked; ?>>
					</td>
				</tr>

					<?= $slika; ?>
					<?= $art_id_hidden; ?>
			<tr>
			<td colspan="2" class="submitCell"><input class="formButton" type="submit" name="submit" id="submit" value ="<?= $action; ?> ARTIKAL">
			</td>
			</tr>
			</table>
		</form>
	</div>