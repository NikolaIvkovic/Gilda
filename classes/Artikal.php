<?php
namespace Classes;
class Artikal extends AbstractBase{
	private $art_id;
	private $art_naziv;
	private $art_prodajna;
	private $art_stanje;
	private $art_alkoholno;
	private $kat_id;
	private $art_slika;
	
	
	public function __construct($art_id){
		$this->art_id = $art_id;
		$sql = 'SELECT * FROM artikli WHERE art_id = :art_id AND art_ponuda = 1';
		$stmt = self::dbConn()->prepare ($sql);
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

	public static function getNazivFromId($art_id){
		$sql = 'SELECT art_naziv FROM artikli WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_naziv'];
	}
	public static function getCena($art_id){
		$sql = 'SELECT art_prodajna FROM artikli WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_prodajna'];
	}
	public static function getPonuda ($art_id) {
		$sql = 'SELECT art_ponuda FROM artikli WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		return $row['art_ponuda'];
	}
	public static function updateArtikal (array $data) {
		
		$sql = 'UPDATE artikli SET
				art_naziv = :art_naziv,
				art_prodajna = :art_prodajna,
				art_stanje = :art_stanje,
				kat_id = :kat_id,
				art_alkoholno = :art_alkoholno
				WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		return $stmt->execute($data);
		
	}
	public static function newArtikal (array $data) {
		$sql = 'INSERT INTO artikli (art_naziv, art_prodajna, art_stanje, kat_id, art_alkoholno, art_slika)
							VALUES (:art_naziv, :art_prodajna, :art_stanje, :kat_id, :art_alkoholno, :art_slika)';
		$stmt = self::dbConn()->prepare($sql);
		return $stmt->execute($data);
	}
	public static function updateStanje(array $data){
		$sql = 'UPDATE artikli SET art_stanje = :art_stanje WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		return $stmt->execute($data);
	}
	public static function deleteArtikal ($art_id) {
		$sql = 'UPDATE artikli SET art_ponuda = 0 WHERE art_id = :art_id';
		$stmt = self::dbConn()->prepare($sql);
		return $stmt->execute(['art_id' => $art_id]);
	}

	
}
?>