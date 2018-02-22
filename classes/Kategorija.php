<?php
namespace Classes;
class Kategorija {
	private $db;
	private $artikli;
	
	
	public function __construct($kat_id, \PDO $db) {
		$this->db = $db;
		$this->artikli = array();
		$sql = 'SELECT art_id, art_prodajna FROM artikli WHERE kat_id = :kat_id ORDER BY art_alkoholno ASC, art_naziv ASC';
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
	public static function getKatName ($kat_id ,\PDO $db) {
		$sql = 'SELECT kat_naziv FROM artikli_kategorije WHERE kat_id = :kat_id';
		$stmt = $db->prepare($sql);
		$stmt->execute(['kat_id'=> $kat_id]);
		$row = $stmt->fetch();
		return $row['kat_naziv'];
	}
	public static function getKategorije (\PDO $db) {
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