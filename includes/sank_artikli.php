<?php
require '../header.php';
$kategorija = new Classes\Kategorija ($_GET['kat_id']);
$artikli = $kategorija->getArtikli();
$tiles = '';
foreach ($artikli as $artikal) {
	$tiles.= '<div class="artikalTile noSelect"
					data-art_prodajna = "'.$artikal->getProdajna().'" 
					style="background-image: url(\''.$artikal->getSlika().'\');"
					data-art_id ="'.$artikal->getArtId().'"><div style="display:inline-block; padding: 30% 0;">'.$artikal->getNaziv().'</div></div>';
}
echo $tiles;
?>