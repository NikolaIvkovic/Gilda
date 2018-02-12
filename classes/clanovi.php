﻿<?php
class Clan {
	private $db;
	private $cl_broj;
	private $cl_imeprezime;
	private $cl_rodjen;
	private $cl_telefon;
	private $cl_email;
	private $cl_facebook;
	private $cl_igre;
	
	public function __construct ($cl_broj, PDO $db) {
		$sql = 'SELECT * FROM clanovi WHERE cl_broj = :cl_broj';
		$stmt = $db->prepare($sql);
		$stmt->execute(['cl_broj' => $cl_broj]);
		$row = $stmt->fetch();
		$this->cl_broj = $cl_broj;
		$this->cl_imeprezime = $row['cl_imeprezime'];
		$this->cl_rodjen = $row['cl_rodjen'];
		$this->cl_telefon = $row['cl_telefon'];
		$this->cl_email = $row['cl_email'];
		$this->cl_facebook = $row['cl_facebook'];
		$this->cl_igre = $row['cl_igre'];	
	}
	public function getImePrezime () {
		return $this->cl_imeprezime;
	}
	public function getRodjen () {
		return $this->cl_rodjen;
	}
	public function getTelefon () {
		return $this->cl_telefon;
	}
	public function getEmail () {
		return $this->cl_email;
	}
	public function getFacebook () {
		return $this->cl_facebook;
	}
	public function getIgre () {
		return $this->cl_igre;
	}
	public static function nextBroj (PDO $db) {
		$sql = 'SELECT cl_broj FROM clanovi ORDER BY cl_broj DESC LIMIT 1';
		$stmt = $db->query($sql);
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
	
	public static function updateClan (array $data, PDO $db) {
		
		$sql = 'UPDATE clanovi SET
				cl_imeprezime = :cl_imeprezime,
				cl_rodjen = :cl_rodjen,
				cl_telefon = :cl_telefon,
				cl_email = :cl_email,
				cl_facebook = :cl_facebook,
				cl_igre = :cl_igre
				WHERE cl_broj = :cl_broj';
		try{$stmt = $db->prepare($sql);
		}
		catch (Exception $e) {
			$_SESSION['errors']['system'][] = $e;
			return false;
		}
		return $stmt->execute($data);
		
	}
	public static function newClan (array $data, PDO $db) {
		$sql = 'INSERT INTO clanovi (cl_broj, cl_imeprezime, cl_rodjen, cl_telefon, cl_email, cl_facebook, cl_igre)
							VALUES (:cl_broj, :cl_imeprezime, :cl_rodjen, :cl_telefon, :cl_email, :cl_facebook, :cl_igre)';
		
		try {
			$stmt = $db->prepare($sql);
		}
		catch (Exception $e) {
			$_SESSION['errors']['system'][] = $e;
			return false;
		}
		return $stmt->execute($data);
		
	}

}
?>