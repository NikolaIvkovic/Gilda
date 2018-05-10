<?php
namespace Classes\PairingSystems;
//interface to be implemented by all pairing system classes to enforce the getPairs function and the PairingSystem type
interface PairingSystem {
	static function getPairs (array $data) : array;
}
?>