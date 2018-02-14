<?php
namespace Classes;
class Artikal {
	private $db;
	private $art_id;
	private $art_naziv;
	private $art_prodajna;
	private $art_stanje;
	private $art_alkoholno;
	private $kat_id;
	private $art_slika;
	
	public function __construct($art_id, PDO $db){
		$this->db = $db;
		$this->art_id = $art_id;
		$sql = 'SELECT * FROM artikli WHERE art_id = :art_id AND art_ponuda = 1';
		$stmt = $this->db->prepare ($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		$this->art_naziv = $row['art_naziv'];
		$this->art_prodajna = $row['art_prodajna'];
		$this->art_stanje = $row['art_stanje'];
		$this->kat_id = $row['kat_id'];
		$this->art_alkoholno = $row['art_alkoholno'];
		$this->art_slika = $row['art_slika'];
	
	}
	public function getArtId() {
		return $this->art_id;
	}
	public function getNaziv() {
		return $this->art_naziv;
	}
	public function getProdajna() {
		return $this->art_prodajna;
	}
	public function getStanje() {
		return $this->art_stanje;
	}
	public function getKatId() {
		return $this->kat_id;
	}
	public function getAlkoholno() {
		return $this->art_alkoholno;
	}
	public function getSlika() {
		return $this->art_slika;
	}

	public static function getNazivFromId($art_id, PDO $db){
		$sql = 'SELECT art_naziv FROM artikli WHERE art_id = :art_id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_naziv'];
	}
	public static function getCena($art_id, PDO $db){
		$sql = 'SELECT art_prodajna FROM artikli WHERE art_id = :art_id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_prodajna'];
	}
	public static function getPonuda ($art_id, PDO $db) {
		$sql = 'SELECT art_ponuda FROM artikli WHERE art_id = :art_id';
		$stmt = $db->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_ponuda'];
	}
	public static function updateArtikal (array $data, PDO $db) {
		
		$sql = 'UPDATE artikli SET
				art_naziv = :art_naziv,
				art_prodajna = :art_prodajna,
				art_stanje = :art_stanje,
				kat_id = :kat_id,
				art_alkoholno = :art_alkoholno
				WHERE art_id = :art_id';
		$stmt = $db->prepare($sql);
		return $stmt->execute($data);
		
	}
	public static function newArtikal (array $data, PDO $db) {
		$sql = 'INSERT INTO artikli (art_naziv, art_prodajna, art_stanje, kat_id, art_alkoholno, art_slika)
							VALUES (:art_naziv, :art_prodajna, :art_stanje, :kat_id, :art_alkoholno, :art_slika)';
		$stmt = $db->prepare($sql);
		return $stmt->execute($data);
	}
	public static function updateStanje(array $data, PDO $db){
		$sql = 'UPDATE artikli SET art_stanje = :art_stanje WHERE art_id = :art_id';
		$stmt = $db->prepare($sql);
		return $stmt->execute($data);
	}
	public static function deleteArtikal ($art_id, PDO $db) {
		$sql = 'UPDATE artikli SET art_ponuda = 0 WHERE art_id = :art_id';
		$stmt = $db->prepare($sql);
		return $stmt->execute(['art_id' => $art_id]);
	}
	public static function nabavka (PDO $db) {
		$sql = 'SELECT art_naziv, art_stanje FROM `artikli` ORDER BY kat_id ASC, art_alkoholno ASC, art_naziv ASC ';
		$stmt = $db->query($sql);
		$odd = true;
		$table = '<table id ="nabavkaTable"><tr><th>NAZIV</th><th>STANJE</th></tr>';
		while ($row = $stmt->fetch()) {
			$style = ($odd) ? '' : ' style="background: #e4e0e0;" ';
			$table .= '<tr '.$style.'><td>'.$row['art_naziv'].'</td>
						<td>'.$row['art_stanje'].'</td></tr>';
			$odd = !$odd;
		}
		$table .= '</table>';
		return $table;
	}
	
}

class Kategorija {
	private $db;
	private $artikli;
	
	
	public function __construct($kat_id, PDO $db) {
		$this->db = $db;
		$this->artikli = array();
		$sql = 'SELECT art_id FROM artikli WHERE kat_id = :kat_id ORDER BY art_alkoholno ASC, art_naziv ASC';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(['kat_id' => $kat_id]);
		while ($row = $stmt->fetch()) {
			if (Artikal::getPonuda($row['art_id'], $db) == true) {
				$this->artikli[] = new Artikal ($row['art_id'], $this->db);
			}
		}
	}
	public function getArtikli () {
		return $this->artikli;
	}
	public static function getKatName ($kat_id ,PDO $db) {
		$sql = 'SELECT kat_naziv FROM artikli_kategorije WHERE kat_id = :kat_id';
		$stmt = $db->prepare($sql);
		$stmt->execute(['kat_id'=> $kat_id]);
		$row = $stmt->fetch();
		return $row['kat_naziv'];
	}
	public static function getKategorije (PDO $db) {
		$sql = 'SELECT * FROM artikli_kategorije';
		$stmt = $db->query($sql);
		$kategorije = array();
		while ($row = $stmt->fetch()) {
			$kategorije[] = $row;
		}
		return $kategorije;
	}
}

?>