<?php
namespace Classes;
class Sanklista {
	public static function orderArtikal (array $data, \PDO $db) {
		$sql = 'INSERT INTO sanklista (art_id, rd_id, cl_broj) VALUES (:art_id, :rd_id, :cl_broj)';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$sql = 'UPDATE artikli SET art_stanje = art_stanje -1 WHERE art_id = '.$data['art_id'];
		$db->query($sql);
		return true;
	}
	public static function payArtikal (array $data, \PDO $db) {
		$sql = 'UPDATE sanklista set sl_placeno = 1 WHERE sl_id = :sl_id';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		return true;
	}
	public static function getAccordionClan (array $data, \PDO $db) {
		$sql = 'SELECT sl.sl_id, sl.art_id, sl.sl_placeno, art.art_naziv 
				FROM sanklista sl  JOIN artikli art
				ON sl.art_id = art.art_id
				WHERE sl.cl_broj = :cl_broj AND sl.rd_id = :rd_id';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$clan = array();
		$imaneplaceno = false;
		while ($row = $stmt->fetch()) {
			if ($row['sl_placeno'] == 0) {
				$imaneplaceno = true;
			}
			if (!isset($clan[$row['art_id']])) {
				$clan[$row['art_id']] = array();
				$clan[$row['art_id']]['art_naziv'] = $row['art_naziv'];
				$clan[$row['art_id']]['stavke'] = array();
			}
			$clan[$row['art_id']]['stavke'][] =array('sl_id' => $row['sl_id'],
													'sl_placeno' => $row['sl_placeno']);
			
		}
		$html = '';
		foreach ($clan as $cl) {
			$html .= '<div class="artikalAccordion">'.$cl['art_naziv'].':&nbsp;';
			foreach ($cl['stavke'] as $st) {
				$class = ($st['sl_placeno'] == 0) ? 'minus' : 'plus';
				$button = ($st['sl_placeno'] == 0) ? '-' : '+';
				$html .= '<div id="'.$st['sl_id'].'" class="stavkaButton '.$class.'">'.$button.'</div>';
			}
			$html.='</div>';
		}
		if ($imaneplaceno && $data['cl_broj'] != 0){
			$sql = 'SELECT SUM(art.art_prodajna) as ukupno 
					FROM `sanklista` sl JOIN artikli art ON sl.art_id = art.art_id 
					WHERE sl.rd_id = :rd_id AND sl.cl_broj = :cl_broj AND sl_placeno = 0
					GROUP BY sl.cl_broj';
			$stmt = $db->prepare($sql);
			$stmt->execute($data);
			$row = $stmt->fetch();
			$html .= '<div class="zaPlacanje">ZA PLAĆANJE: '.$row['ukupno'].' DIN</div>';
		}
	return $html;
	}
	public static function rebuildAccordion(array $data, \PDO $db)  {
		$accordion = array();
		$sql = 'SELECT sl.cl_broj, cl.cl_imeprezime 
				FROM sanklista sl JOIN clanovi cl 
				ON sl.cl_broj = cl.cl_broj
				WHERE rd_id = :rd_id';
		$stmt = $db->prepare($sql);
		$stmt->execute ($data);
		while ($row = $stmt->fetch()) {
			$accordion[$row['cl_broj']] = $row['cl_imeprezime'];
		}
		$accordion = array_unique($accordion);
		if (isset($accordion[0])) {
			unset($accordion[0]);
		}
		return $accordion;
	}
	public static function getListTable (array $data, \PDO $db) {
		$month = new \DateTime($data['month']);
		$data = array();
		$data['start'] = $month->format('Y-m-d H:i:s');
		$month->add(new \DateInterval('P1M'));
		$data['end'] = $month->format('Y-m-d H:i:s');
		$sql = 'SELECT sl.rd_id, sl.rd_start, sum(art.art_prodajna) AS ukupno
				FROM
				(SELECT s.rd_id, s.sl_placeno, r.rd_start, s.art_id FROM sanklista s JOIN radni_dani r ON s.rd_id = r.rd_id) sl
				JOIN artikli art
				ON sl.art_id = art.art_id
				WHERE sl.sl_placeno = 1 AND sl.rd_start >= :start AND sl.rd_start <= :end
				GROUP BY sl.rd_id
				ORDER BY sl.rd_id DESC';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$table = '';
		$table .= '<div class="obracun">UKUPNO: <span id="obracunCalc">0</span> DIN.</div>';
		$table .= '<table id="sanklista" class="liste">';
		$odd = true;
		$dani = array( '',
						'Ponedeljak', 
						'Utorak', 
						'Sreda',
						'Četvrtak',
						'Petak',
						'Subota',
						'Nedelja');
		while ($row = $stmt->fetch()) {
			$rowclass = ($odd) ? 'odd' : 'even';
			$datum = new \DateTime($row['rd_start']);

			$table .= '<tr class="listRow '.$rowclass.'" id="'.$row['rd_id'].'">
					<td><input class="checkObracun" type="checkbox" ></td>
					<td>'.$dani[$datum->format('N')].'</td>
					<td>'.$datum->format('d.m.Y').'</td>
					<td class="ukupno">'.number_format($row['ukupno'], 0, ',', '.').' DIN</td>
					
					</tr>';
			$odd = !$odd;
		}

		$table .= '</table>';
		return $table;
	}
	public static function getSankLista(array $data, \PDO $db) {
		$sql = 'SELECT art.art_naziv, art.art_prodajna, COUNT(sl.sl_placeno) AS prodato, art.art_stanje
				FROM sanklista sl JOIN artikli art ON sl.art_id = art.art_id  
				WHERE sl.rd_id = :rd_id AND sl_placeno = 1
				GROUP BY sl.art_id 
				ORDER BY art.kat_id DESC, art.art_alkoholno DESC, art.art_naziv ASC';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$odd = true;
		$sanklista['placeno'] = '<div class="naslov">ŠANK LISTA</div>
								<table class="dnevnaLista">
					<tr><th>Naziv</th><th>Cena</th><th>Prodato</th><th>Ukupno</th><th>Stanje</th></tr>';
		while ($row = $stmt->fetch()){
			$ukupno = $row['art_prodajna'] * $row['prodato'];
			$class = ($odd) ? 'odd' : 'even';
			$sanklista['placeno'] .= '<tr class="'.$class.'">
						<td>'.$row['art_naziv'].'</td>
						<td>'.$row['art_prodajna'].'</td>
						<td>'.$row['prodato'].'</td>
						<td>'.$ukupno.'</td>
						<td>'.$row['art_stanje'].'</td>';
			$odd = !$odd;
		}
		$sanklista['placeno'] .= '</table>';
		$sql = 'SELECT art.art_naziv,art.art_prodajna, sl.sl_id, sl.cl_imeprezime FROM
				(SELECT s.sl_id, s.art_id, s.cl_broj, s.rd_id, s.sl_placeno, c.cl_imeprezime FROM sanklista s JOIN clanovi c
				ON s.cl_broj = c.cl_broj) sl JOIN artikli art ON
				sl.art_id = art.art_id
				WHERE sl.sl_placeno = 0 AND sl.rd_id = :rd_id';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$sanklista['neplaceno'] = '<table class="neplaceno"><tr><th colspan="3">NEPLAĆENO</th></tr>
									<tr><th>Naziv</th><th>Cena</th><th>Clan</th></tr>';
		while ($row = $stmt->fetch()) {
			$sanklista['neplaceno'] .= '<tr><td>'.$row['art_naziv'].'</td>
							<td>'.$row['art_prodajna'].'</td>
							<td>'.$row['cl_imeprezime'].'</td>
							<td><div class="stavkaButton minus" data-sl_id="'.$row['sl_id'].'">-</div></td></tr>';
		}
		$sanklista['neplaceno'] .= '</table>';
		$sanklista['napomene'] = '<div id="addNapomena" class="formButton" data-rd_id ="'.$data['rd_id'].'">DODAJ NAPOMENU</div>';
		$sql = 'SELECT np_sadrzaj FROM napomena WHERE rd_id = '.$data['rd_id'];
		$stmt = $db->query($sql);
		//var_dump($stmt->fetch());
		if ($stmt->rowCount()) {
			while ($row = $stmt->fetch()) {
				$sanklista['napomene'] .= '<div class="napomene">'.$row['np_sadrzaj'].'</div>';
			}
		}
		return $sanklista;
	}
	public static function newRadniDan(\PDO $db) {
		$sql = 'INSERT INTO radni_dani (rd_stop) values (null)';
		$db->query($sql);
		return $db->lastinsertid();
	}
	public static function endRadniDan(array $data, \PDO $db) {
		$sql = 'UPDATE radni_dani SET rd_stop = CURRENT_TIMESTAMP WHERE rd_id = :rd_id';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		echo $data['rd_id'];
	}
	public static function newNapomena (array $data, \PDO $db) {
		$sql = 'INSERT INTO napomena (rd_id, np_sadrzaj) VALUES (:rd_id, :np_sadrzaj)';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
	}
	public static function getDuznici (array $data = NULL, \PDO $db) {
		$rd_condition = '';
		if (isset($data)) {
			$rd_condition = ' AND NOT s.rd_id = '.$data['rd_id'];
		}
		$sql = 'SELECT sum(a.art_prodajna) AS dug, c.cl_imeprezime, c.cl_broj
				FROM artikli a JOIN sanklista s ON a.art_id = s.art_id
				JOIN clanovi c ON s.cl_broj = c.cl_broj
				WHERE NOT s.cl_broj = 0 AND NOT s.cl_broj = 270 AND NOT s.sl_placeno = 1 '.$rd_condition.' 
				GROUP BY s.cl_broj';
		$stmt = $db->query($sql);
		if ($stmt->rowCount()) {
			$odd = true;
			$duznici = '<table class="clanlista" style="width: 400px;"><tr><th>Ime i Prezime</th><th>Dug</th></tr>';
			while ($row = $stmt->fetch()) {
				$rowclass = ($odd) ? 'odd' : 'even';
				$duznici .= '<tr class="'.$rowclass.'"><td class="duznik" id="'.$row['cl_broj'].'">'.$row['cl_imeprezime'].'</td><td>'.$row['dug'].'DIN</td></tr>';
				$odd = !$odd;
			}
			$duznici.= '</table><div id="duznikDialog"></div>';
			return $duznici;
		}
	}
	public static function getDuznikArtikli(array $data, \PDO $db) {
		$sql = 'SELECT a.art_naziv, a.art_prodajna, r.rd_start FROM
				artikli a JOIN sanklista s ON a.art_id = s.art_id
				JOIN radni_dani r ON s.rd_id = r.rd_id
				WHERE s.cl_broj = :cl_broj AND s.sl_placeno = 0';
		$stmt = $db->prepare($sql);
		$stmt->execute($data);
		$duznik = '<table class="clanlista" style="width:520px;"><tr><th>Artikal</th><th>Cena</th><th>Kupljeno</th></tr>';
		$odd = true;
			while ($row = $stmt->fetch()) {
				$rowclass = ($odd) ? 'odd' : 'even';
				$datum = new \DateTime($row['rd_start']);
				$duznik .= '<tr class="'.$rowclass.'"><td>'.$row['art_naziv'].'</td><td>'.$row['art_prodajna'].'DIN</td><td>'.$datum->format('d.m.Y').'</td></tr>';
				$odd = !$odd;
			}
			$duznik.= '</table>';
		return $duznik;
	}
}
?>