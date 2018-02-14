<?php
require 'header.php';
include APP_DIR.'classes/artikli.php';
$kategorija = new kategorija ($kat_id, $db);
$artikli = $kategorija->getArtikli();
$tablerows = '';
foreach ($artikli as $a) {
	$alkoholno = ($a->getAlkoholno() == 1) ? 'Da' : 'Ne';
	$link = '<a class="artikalLink" href="#" data-art_id ="'.$a->getArtId().'">'.$a->getnaziv().'</a>';
	$tablerows .= '<tr>
					<td><img class="artThumbnail" src="'.$a->getSlika().'"></td>
					<td>'.$link.'</td>
					<td>'.$a->getProdajna().'</td>
					<td><input type="text" size="3" class="artStanje" data-art_id="'.$a->getArtId().'" value="'.$a->getStanje().'"></td>
					<td>'.$alkoholno.'</td>
					<td><a href="#" class="delButton whitelink" data-art_id ="'.$a->getArtId().'">X</a></td>
					</tr>';
}
?>
		<script>

		</script>
	<table class="lista">
		<form action="" method="POST">
			<tr>
			<th>&nbsp;</th>
			<th>NAZIV</th>
			<th>PRODAJNA<br> CENA</th>
			<th>STANJE NA<br> LAGERU</th>
			<th>ALKOHOLNO</th>

			</tr>
			<?=$tablerows;?>
			<tr class="noborderRow">
			<td colspan="3"></td>
			<td><input type="submit" class="formButton" name="submit" id="azuriraj" value="AŽURIRAJ STANJE"></td>
			</tr>
		</form>
	</table>
	<div id="artDialog"></div>

