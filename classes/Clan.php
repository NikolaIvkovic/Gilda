<?php
namespace Classes;
class Clan extends AbstractBase{
	protected $broj;
	protected $imePrezime;
	protected $rodjen;
	protected $telefon;
	protected $email;
	protected $facebook;
	protected $igre;
	
	
	public function __construct ($cl_broj) {
		$sql = 'SELECT * FROM clanovi WHERE cl_broj = :cl_broj';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['cl_broj' => $cl_broj]);
		$row = $stmt->fetch();
		$this->broj = $cl_broj;
		$this->imePrezime = $row['cl_imeprezime'];
		$this->rodjen = $row['cl_rodjen'];
		$this->telefon = $row['cl_telefon'];
		$this->email = $row['cl_email'];
		$this->facebook = $row['cl_facebook'];
		$this->igre = $row['cl_igre'];	
	}

	public static function nextBroj () {
		$sql = 'SELECT cl_broj FROM clanovi ORDER BY cl_broj DESC LIMIT 1';
		$stmt = self::dbConn()->query($sql);
		$row = $stmt->fetch();
		return $row['cl_broj'] + 1;
	}
	public static function dateForm2Db($date) {
		$date = rtrim($date, '.');
		$arr = array();
		$temp = explode('.', $date);
			foreach ($temp as $value) {
				$arr[] = intval($value);
			}
		if (count($arr) == 3){
			return date('Y-m-d', mktime(0, 0, 0, $arr[1], $arr[0], $arr[2]));
		}
		else {
			$_SESSION['errors']['validation'][] = 'Datum je nepravilno unet!';
			return false;
		}
	}
	public static function dateDb2Form($date){
		$tempdate = explode('-', $date);
		return date ('d.m.Y', mktime(0, 0, 0, $tempdate[1], $tempdate[2], $tempdate[0]));
	}
	
	public static function updateClan (array $data) {
		
		$sql = 'UPDATE clanovi SET
				cl_imeprezime = :cl_imeprezime,
				cl_rodjen = :cl_rodjen,
				cl_telefon = :cl_telefon,
				cl_email = :cl_email,
				cl_facebook = :cl_facebook,
				cl_igre = :cl_igre
				WHERE cl_broj = :cl_broj';
		try{$stmt = self::dbConn()->prepare($sql);
		}
		catch (Exception $e) {
			$_SESSION['errors']['system'][] = $e;
			return false;
		}
		return $stmt->execute($data);
		
	}
	public static function newClan (array $data) {
		$sql = 'INSERT INTO clanovi (cl_broj, cl_imeprezime, cl_rodjen, cl_telefon, cl_email, cl_facebook, cl_igre)
							VALUES (:cl_broj, :cl_imeprezime, :cl_rodjen, :cl_telefon, :cl_email, :cl_facebook, :cl_igre)';
		
		try {
			$stmt = self::dbConn()->prepare($sql);
		}
		catch (Exception $e) {
			$_SESSION['errors']['system'][] = $e;
			return false;
		}
		return $stmt->execute($data);
		
	}

}
?>