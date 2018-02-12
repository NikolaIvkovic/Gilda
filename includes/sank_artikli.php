<?php
require '../header.php';
include APP_DIR.'classes/artikli.php';
$kategorija = new Kategorija ($_GET['kat_id'], $db);
$artikli = $kategorija->getArtikli();
$tiles = '';
foreach ($artikli as $artikal) {
	$tiles.= '<div class="artikalTile"
					style="background-image: url(\''.$artikal->getSlika().'\');"
					data-art_id ="'.$artikal->getArtId().'"><div style="display:inline-block; padding: 30% 0;">'.$artikal->getNaziv().'</div></div>';
}
echo $tiles;
?>