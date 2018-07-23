<?php
namespace Classes\PairingSystems;
class SwissUnique extends \Classes\AbstractBase implements PairingSystem {
	static function getPairs(array $data) : array {
		$playerList = $data['playerList'];
		$pairs = $data['pairs'];
		$newPairs = [];
		$matched = [];
		foreach ($playerList as $key => $val) {
			$matchFound = false;
			//check to see if current key has already been matched
			if (!in_array($key, $matched)) {
				$i = 1;				
				while ($matchFound == false && $i < count($playerList) + 1) {
					if (isset($playerList[$key+$i]) AND !in_array($key+$i, $matched) AND !in_array([$val->getPlId(), $playerList[$key+$i]->getPlId()], $pairs) AND !in_array([$playerList[$key+$i]->getPlId(), $val->getPlId()], $pairs)) {

						$matchFound = true;
						$matched[] = $key;
						$matched[] = $key + $i;
						$newPairs[] = [$val, $playerList[$key + $i]];
					}
					else {
						$i++;
						if ($i >= count($playerList)) {
							reset($playerList);
						}

					}
					
				}
			} 
		}
		//check to see if there are players that couldn't be matched and force pairings
		if (count($newPairs) != count($playerList)/2) {
			$unmatched = [];
			foreach ($playerList as $key => $val) {
				if (!in_array($key, $matched)) {
					$unmatched[] = $val;
				}
			}
			$i = count($newPairs);
			foreach($unmatched as $player) {
				$newPairs[$i][] = $player;
				if (count($newPairs[$i]) == 2) {
					$i++;
				}
			}
		}
		return $newPairs;
	}
	
}
?>