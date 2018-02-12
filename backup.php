<?php
$sql = 'SELECT * FROM artikli';
$stmt = $db->query($sql);
$tables['artikli'] = 'INSERT INTO `artikli`(`art_id`, `art_naziv`, `art_prodajna`, `art_stanje`, `kat_id`, `art_alkoholno`, `art_slika`, `art_ponuda`) VALUES <br>';
while ($r = $stmt->fetch()) {
	$tables['artikli'] .= '('.$r['art_id'].', "'.$r['art_naziv'].'", '.$r['art_prodajna'].', '.$r['art_stanje'].', '.$r['kat_id'].', '.$r['art_alkoholno'].', "'.$r['art_slika'].'", '.$r['art_ponuda'].'), <br>';
}
$tables['artikli'] = rtrim($tables['artikli'], ', <br>').';';
$sql = 'SELECT * FROM artikli_kategorije';
$stmt = $db->query($sql);
$tables['artikli_kategorije'] = 'INSERT INTO `artikli_kategorije`(`kat_id`, `kat_naziv`) VALUES <br>';
while($r = $stmt->fetch()) {
	$tables['artikli_kategorije'] .= '('.$r['kat_id'].', "'.$r['kat_naziv'].'"), <br>';
}
$tables['artikli_kategorije'] = rtrim($tables['artikli_kategorije'], ', <br>').';';
$sql = 'SELECT * FROM clanovi';
$stmt = $db->query($sql);
$tables['clanovi'] = 'INSERT INTO `clanovi`(`cl_broj`, `cl_imeprezime`, `cl_rodjen`, `cl_telefon`, `cl_email`, `cl_facebook`, `cl_igre`) VALUES <br>';
while ($r = $stmt->fetch()) {
	$tables['clanovi'] .= '('.$r['cl_broj'].', "'.$r['cl_imeprezime'].'", "'.$r['cl_rodjen'].'", "'.$r['cl_telefon'].'", "'.$r['cl_email'].'", "'.$r['cl_facebook'].'", "'.$r['cl_igre'].'"), <br>';
}
$tables['clanovi'] = rtrim($tables['clanovi'], ', <br>').';';

$sql = 'SELECT * FROM napomene';
$stmt = $db->query($sql);
$tables['napomene'] = 'INSERT INTO `napomene`(`np_id`, `rd_id`, `np_sadrzaj`) VALUES <br>';
while($r = $stmt->fetch()) {
	$tables['napomene'] .= '('.$r['np_id'].', '.$r['rd_id'].', "'.$r['np_sadrzaj'].'"), <br>';
}
$tables['napomene'] = rtrim($tables['napomene'], ', <br>').';';

$sql = 'SELECT * FROM radni_dani';
$stmt = $db->query($sql);
$tables['radni_dani'] = 'INSERT INTO `radni_dani`(`rd_id`, `rd_start`, `rd_stop`) VALUES <br>';
while($r = $stmt->fetch()) {
	$tables['radni_dani'] .= '('.$r['rd_id'].', "'.$r['rd_start'].'", "'.$r['rd_stop'].'"), <br>';
}
$tables['radni_dani'] = rtrim($tables['radni_dani'], ', <br>').';';

$sql = 'SELECT * FROM sanklista';
$stmt = $db->query($sql);
$tables['sanklista'] = 'INSERT INTO `sanklista`(`sl_id`, `art_id`, `rd_id`, `sl_placeno`, `cl_broj`) VALUES <br>';
while($r = $stmt->fetch()) {
	$tables['sanklista'] .= '('.$r['sl_id'].', '.$r['art_id'].', '.$r['rd_id'].', '.$r['sl_placeno'].', '.$r['cl_broj'].'), <br>';
}
$tables['sanklista'] = rtrim($tables['sanklista'], ', <br>').';';

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: gildaigraca@zoho.eu\r\n";
$headers .= "Reply-To: gildaigraca@zoho.eu\r\n";
$headers .= "Return-Path: gildaigraca@zoho.eu\r\n";
$msg = '<pre>'.implode('<br>', $tables).'</pre>';
$subject = 'backup za Gildu - '.date('d.m.Y');
mail("gildaigraca@zoho.eu",$subject,$msg, $headers);
mail("theserbianraven@gmail.com",$subject,$msg, $headers);
?>