<?php
namespace Classes;
class Player extends TMBase {
	protected $plId;
	protected $plName;
	protected $plFaction;
	
	public function __construct (array $data) {
		$this->plId = $data['pl_id'];
		$this->plName = $data['pl_name'];
		$this->plFaction = $data['pl_faction'];
	}
	static function getPlayers($toId, $sortingTable) {
		$playerList = [];
		$sql = 'SELECT pl_id, pl_name, pl_faction FROM '.$sortingTable.' WHERE to_id = '.$toId;
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$playerList[] = new self($row);
		}
		return $playerList;
	}
	static function setPlayers ($table, $id, $players) {
		$values = '';
		foreach($players as $player) {
			$values .= '('.$player['pl_id'].', "'.$player['pl_name'].'", "'.$player['pl_faction'].'", "'.$id.'"), ';
		}
		$sql = 'INSERT INTO `'.$table.'` (pl_id, pl_name, pl_faction, to_id) VALUES '.rtrim($values, ', ');
		self::dbConn()->query($sql);
	}
	static function savePairs(array $pairs) {
		$values = '';
		foreach($pairs as $pair) {
			$values .= '('.$pair['to_id'].', "'.$pair['rnd'].'", "'.$pair['pl1_id'].'", "'.$pair['pl2_id'].'"), ';
		}
		$sql = 'INSERT INTO pairs (to_id, rnd, pl1_id, pl2_id) VALUES '.rtrim($values, ', ');
		self::dbConn()->query($sql);
	}
	static function getPairs($toId, $sortingTable) {
		$pairs = [];
		$sql = 'SELECT p.rnd,
				p.pl1_id, p1.pl_name pl1_name, p1.pl_faction pl1_faction, 
				p.pl2_id, p2.pl_name pl2_name, p2.pl_faction pl2_faction 
				FROM pairs p JOIN '.$sortingTable.' p1 
				ON p.pl1_id = p1.pl_id 
				JOIN '.$sortingTable.' p2 
				ON p.pl2_id = p2.pl_id
				WHERE p.to_id = '.$toId.' 
				ORDER BY p.rnd ASC';
		$stmt = self::dbConn()->query($sql);
		$round = 1;
		$pair = 1;
		while ($row = $stmt->fetch()) {
			if ($round < $row['rnd']) {
				$round++;
				$pair = 1;
			}

			$pairs[$round][$pair]['pl1'] = new self([
											'pl_id' => $row['pl1_id'],
											'pl_name' => $row['pl1_name'],
											'pl_faction' =>$row['pl1_faction']
											]);
			$pairs[$round][$pair]['pl2'] = new self([
											'pl_id' => $row['pl2_id'],
											'pl_name' => $row['pl2_name'],
											'pl_faction' =>$row['pl2_faction']
											]);
		$pair++;
		}
		$pairsHTML = '<div class="pairsWrapper">';
		foreach ($pairs as $key => $round) {
			$pairsTable = '<table class="pairsTable">';
			$pairsTable .= '<tr><th colspan="3">'.$key.'. RUNDA</th> </tr>';
			$odd = true;
			foreach ($round as $pair) {
				$rowClass = ($odd) ? 'odd' : 'even';
				$pairRow = '<tr class="'.$rowClass.'">';
				$pairRow.= '<td>'.$pair['pl1']->getPlName().'<br><span class="faction">'.$pair['pl1']->getPlFaction().'</span></td>';
				$pairRow.= '<td>VS</td>';
				$pairRow.= '<td>'.$pair['pl2']->getPlName().'<br><span class="faction">'.$pair['pl2']->getPlFaction().'</span></td>';
				$pairRow.= '</tr>';
				$pairsTable .= $pairRow;
				$odd = !$odd;
			}
			$pairsTable .= '</table>';
			$pairsHTML .= $pairsTable;
		}
		$pairsHTML .= '</div>';
		return $pairsHTML;
	}
	public static function getCurrentPairs($currentRound, $sortingTable, $toId) {
		$pairs = [];
		$sql = 'SELECT 
				p.pl1_id, p1.pl_name pl1_name, p1.pl_faction pl1_faction, 
				p.pl2_id, p2.pl_name pl2_name, p2.pl_faction pl2_faction 
				FROM pairs p JOIN '.$sortingTable.' p1 
				ON p.pl1_id = p1.pl_id 
				JOIN '.$sortingTable.' p2 
				ON p.pl2_id = p2.pl_id
				WHERE p.rnd = '.$currentRound.' AND p.to_id = '.$toId.'
				ORDER BY p.rnd ASC';
		$stmt = self::dbConn()->query($sql);
		$i = 0;
		while ($row = $stmt->fetch()) {
			
			$pairs[$i]['pl1'] = new self([
											'pl_id' => $row['pl1_id'],
											'pl_name' => $row['pl1_name'],
											'pl_faction' =>$row['pl1_faction']
											]);
			$pairs[$i]['pl2'] = new self([
											'pl_id' => $row['pl2_id'],
											'pl_name' => $row['pl2_name'],
											'pl_faction' =>$row['pl2_faction']
											]);
		$i++;
		}
	return $pairs;
	}
}
?>