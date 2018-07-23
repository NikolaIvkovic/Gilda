<?php
namespace Classes;
class Player extends AbstractBase {
	protected $plId;
	protected $plName;
	protected $plFaction;
	
	public function __construct (array $data) {
		$this->plId = $data['cl_broj'];
		$this->plName = $data['pl_name'];
		$this->plFaction = $data['pl_faction'];
	}
	//get all players for given tournament
	static function getPlayers($toId, $sortingTable) {
		$playerList = [];
		$sql = 'SELECT cl_broj, pl_name, pl_faction FROM '.$sortingTable.' WHERE to_id = '.$toId;
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$playerList[] = new self($row);
		}
		return $playerList;
	}
	//insert players participating in current tournament into players table
	static function setPlayers ($table, $id, $players) {
		$values = '';
		foreach($players as $player) {
			$values .= '('.$player['cl_broj'].', "'.$player['pl_name'].'", "'.$player['pl_faction'].'", "'.$id.'"), ';
		}
		$sql = 'INSERT INTO '.$table.' (cl_broj, pl_name, pl_faction, to_id) VALUES '.rtrim($values, ', ');
		self::dbConn()->query($sql);
	}
	//insert pairs for surrent round into pairs table
	static function savePairs(array $pairs) {
		$values = '';
		foreach($pairs as $pair) {
			$values .= '('.$pair['to_id'].', "'.$pair['rnd'].'", "'.$pair['pl1_id'].'", "'.$pair['pl2_id'].'"), ';
		}
		$sql = 'INSERT INTO pairs (to_id, rnd, pl1_id, pl2_id) VALUES '.rtrim($values, ', ');
		self::dbConn()->query($sql);
	}
	//get tables with all existing rounds and their pairings
	static function getPairs($toId, $sortingTable) {
		$pairs = [];
		$sql = 'SELECT p.rnd,
				p.pl1_id, p1.pl_name pl1_name, p1.pl_faction pl1_faction, 
				p.pl2_id, p2.pl_name pl2_name, p2.pl_faction pl2_faction 
				FROM pairs p JOIN '.$sortingTable.' p1 
				ON p.pl1_id = p1.cl_broj 
				JOIN '.$sortingTable.' p2 
				ON p.pl2_id = p2.cl_broj
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
											'cl_broj' => $row['pl1_id'],
											'pl_name' => $row['pl1_name'],
											'pl_faction' =>$row['pl1_faction']
											]);
			$pairs[$round][$pair]['pl2'] = new self([
											'cl_broj' => $row['pl2_id'],
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
	//get pairs for the round in progress
	public static function getCurrentPairs($currentRound, $sortingTable, $toId) {
		$pairs = [];
		$sql = 'SELECT 
				p.pl1_id, p1.pl_name pl1_name, p1.pl_faction pl1_faction, 
				p.pl2_id, p2.pl_name pl2_name, p2.pl_faction pl2_faction 
				FROM pairs p JOIN '.$sortingTable.' p1 
				ON p.pl1_id = p1.cl_broj 
				JOIN '.$sortingTable.' p2 
				ON p.pl2_id = p2.cl_broj
				WHERE p.rnd = '.$currentRound.' AND p.to_id = '.$toId.'
				ORDER BY p.rnd ASC';
		$stmt = self::dbConn()->query($sql);
		$i = 0;
		while ($row = $stmt->fetch()) {
			$pairs[$i]['pl1'] = new self([
											'cl_broj' => $row['pl1_id'],
											'pl_name' => $row['pl1_name'],
											'pl_faction' =>$row['pl1_faction']
											]);
			$pairs[$i]['pl2'] = new self([
											'cl_broj' => $row['pl2_id'],
											'pl_name' => $row['pl2_name'],
											'pl_faction' =>$row['pl2_faction']
											]);
		$i++;
		}
	return $pairs;
	}
	//insert new players into rankings table and update existing ones
	public static function updateRankings (Tournament $tournament) {
		$data = [];
		$columns = implode(', ', $tournament->getGame()->getColumns());
		$orderBy = implode (' DESC, ', $tournament->getGame()->getColumns()).' DESC';
		$sql = 'SELECT cl_broj, '.$columns.' FROM '.$tournament->getSortingTable().'
				WHERE cl_broj != 999 ORDER BY '.$orderBy;
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$cl_broj = $row['cl_broj'];
			unset($row['cl_broj']);
			$rnk_scores = serialize ($row);
			$data[] = ['cl_broj' => $cl_broj,
						'gm_id' => $tournament->getGame()->getId(),
						'rnk_scores' => $rnk_scores,
						'rnk_year' => date('Y')];
		}
		$winnerProcessed = false;
		foreach ($data as $player) {
			$newScores = unserialize($player['rnk_scores']);
			//retrieve existing scores (if available) and add them to the new values
			$sql = 'SELECT rnk_scores FROM rankings WHERE cl_broj = '.$player['cl_broj'];
			$stmt = self::dbConn()->query($sql);
			$row = $stmt->fetch();
			if ($row) {
				$oldScores = unserialize($row['rnk_scores']);
				$columns = array_keys($oldScores);
				foreach ($columns as $column) {
					$newScores[$column] = $newScores[$column] + $oldScores[$column];
				}
			}
			//Strength of Schedule is used only for in-torunament rankings and needs to be removed from the newScores array
			if (isset($newScores['SoS'])) {
				unset($newScores['SoS']);
			}
			$player['rnk_scores'] = serialize($newScores);
			//enter new data into rankings table
			$sql = 'INSERT INTO rankings (cl_broj, gm_id, rnk_scores, rnk_year)
					VALUES (:cl_broj, :gm_id, :rnk_scores, :rnk_year)
					ON DUPLICATE KEY UPDATE
					rnk_scores = :rnk_scores';
			$stmt = self::dbConn()->prepare($sql);
			$stmt->execute($player);
			//Increment rnk_tournamentsWon by 1 for the top ranked player
			$winnerSql = "UPDATE rankings SET rnk_tournamentsWon = rnk_tournamentsWon + 1
							WHERE cl_broj = {$player['cl_broj']} AND gm_id = {$player['gm_id']} AND rnk_year = {$player['rnk_year']}";
			if (!$winnerProcessed) {
				self::dbConn()->query($winnerSql);
			}
			
			$winnerProcessed = true;	
		}
				
		
	}
}
?>