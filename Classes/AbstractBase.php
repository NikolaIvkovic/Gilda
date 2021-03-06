<?php
namespace Classes;

abstract class AbstractBase {
	protected static $dbhost = null;
	
	static function dbConn() {
		if (self::$dbhost == null) {
			$opt = array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC);
			self::$dbhost = new \PDO ('mysql:host=localhost;dbname=gildadb;charset=utf8;', 'root', '', $opt);
		}
		return self::$dbhost;
	}
	public function __call ($func, $args) {
		$action = substr ($func, 0, 3);
		$property = lcfirst(substr($func, 3));
		if ($action === 'set') {
			$this->$property = $args[0];
		} elseif ($action ==='get') {
			
			return $this->$property;
		}
		else {
			throw new Exception('Method '.$func.' does not exist and is not recognized as a valid get/set method.');
		}
	}	
}
?>