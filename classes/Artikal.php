<?php
namespace Classes;
class Artikal extends AbstractBase{
	protected $artId;
	protected $naziv;
	protected $prodajna;
	protected $stanje;
	protected $alkoholno;
	protected $katId;
	protected $slika;
	
	
	public function __construct($art_id){
		$this->artId = $art_id;
		$sql = 'SELECT * FROM artikli WHERE art_id = :art_id AND art_ponuda = 1';
		$stmt = self::dbConn()->prepare ($sql);
		$stmt->execute(['art_id' => $art_id]);
		$row = $stmt->fetch();
		$this->naziv = $row['art_naziv'];
		$this->prodajna = $row['art_prodajna'];
		$this->stanje = $row['art_stanje'];
		$this->katId = $row['kat_id'];
		$this->alkoholno = $row['art_alkoholno'];
		$this->slika = $row['art_slika'];
	
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