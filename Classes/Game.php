<?php
namespace Classes;
class Game extends TMBAse {
	public static $gmImgPath = 'img/games/';
	protected $id;
	protected $name;
	protected $img;
	protected $columns;
	
	public function __construct($id) {
		$sql = 'SELECT * FROM game WHERE gm_id = :gm_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['gm_id' => $id]);
		$row = $stmt->fetch();
		$this->id = $id;
		$this->name = $row['gm_name'];
		$this->img = self::$gmImgPath.$row['gm_img'];
		$this->columns = unserialize($row['gm_columns']);
	}
	public static function getGameList() {
		$sql = 'SELECT * FROM game';
		$stmt = self::dbConn()->query($sql);
		$result = [];
		while ($row = $stmt->fetch()) {
			$result [] = $row;
		}
		return $result;
	}
}
?>