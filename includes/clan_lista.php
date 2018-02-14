<?php
require 'header.php';
include APP_DIR.'classes/clanovi.php';
include APP_DIR.'classes/paginator.php';
$pag = new paginator($db);
$pag->setpageqstr('page');
$pag->setpagesize(20);
$pag->getnumrows('clanovi');
echo '<div class="pageWrapper">';
$pag->paginate();
$offset =  ($pag->currentpage * $pag->pagesize) - $pag->pagesize;
$sql = 'SELECT * FROM clanovi ORDER BY cl_imeprezime ASC LIMIT :limit OFFSET  :offset';
$stmt = $db->prepare($sql);
$stmt->bindValue(':limit', (int) $pag->pagesize, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();

$odd = true;
echo '<table class ="clanlista"><tr><th>Br</th><th>Ime i prezime</th><th style="width: 10px;">Rođen</th><th>Tel</th><th>E-mail</th><th>Facebook</th><th style="width:20%">Omiljene igre</th></tr>';
	while ($row = $stmt->fetch()) {
		$datum = clan::dateDb2Form($row['cl_rodjen']);
		$rowclass = ($odd) ? 'odd' : 'even';
		$odd = !$odd;
		echo '<tr class="'.$rowclass.'">';
		echo '<td>'.$row['cl_broj'].'</td>';
		echo '<td><a class="clanLink" href="#" data-cl_broj ="'.$row['cl_broj'].'">'.$row['cl_imeprezime'].'</a></td>';
		echo '<td>'.$datum.'</td>';
		echo '<td style="text-align: center;">'.$row['cl_telefon'].'</td>';
		echo '<td>'.$row['cl_email'].'</td>';
		echo '<td>'.$row['cl_facebook'].'</td>';
		echo '<td style="font-size: small;">'.$row['cl_igre'].'</td>';
		echo '</tr>';
		
	}
echo '</div>';
echo '<div id="clDialog"></div>';
?>