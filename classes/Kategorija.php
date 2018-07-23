<?php
namespace Classes;
class Kategorija extends AbstractBase{
	protected $artikli;
	
	
	public function __construct($kat_id) {
		$this->artikli = array();
		$sql = 'SELECT art_id, art_prodajna FROM artikli WHERE kat_id = :kat_id ORDER BY art_alkoholno ASC, art_naziv ASC';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['kat_id' => $kat_id]);
		while ($row = $stmt->fetch()) {
			if (Artikal::getPonuda($row['art_id']) == true) {
				$this->artikli[] = new Artikal ($row['art_id'], self::dbConn());
			}
		}
	}

	public static function getKatName ($kat_id) {
		$sql = 'SELECT kat_naziv FROM artikli_kategorije WHERE kat_id = :kat_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['kat_id'=> $kat_id]);
		$row = $stmt->fetch();
		return $row['kat_naziv'];
	}
	public static function getKategorije () {
		$sql = 'SELECT * FROM artikli_kategorije';
		$stmt = self::dbConn()->query($sql);
		$kategorije = array();
		while ($row = $stmt->fetch()) {
			$kategorije[] = $row;
		}
		return $kategorije;
	}
}
?>