<?php
namespace Classes;
class Tournament  extends AbstractBase{
	protected $id;
	protected $name;
	protected $toDate;
	protected $sortingTable;
	protected $game;
	protected $optMaxRounds;
	protected $optCurrentRound;
	protected $optPairingSystem;
	protected $optByeValues;
	
	public function __construct (array $data) {
		//if data['to_id'] is set it means only the to_id value for an existing torunament was passed. The $data variable will be overwritten by data from the Tournament table.
		$existingTournament = (isset($data['to_id'])) ? true : false;
		$data = (isset($data['to_id'])) ? $this->getTournamentData($data['to_id']) : $data;
		//setting Tournament properties
		$opt = (isset($data['to_options'])) ? ($data['to_options']) : null;
		$this->name = (isset($data['to_name'])) ? $data['to_name'] : '';
		$this->toDate = (isset($data['to_date'])) ? $data['to_date'] : date('Y-m-d');
		$this->game = new Game($data['to_game']);
		$this->sortingTable = (isset($data['to_sortingTable'])) ? $data['to_sortingTable'] : 'tmp_'.substr(sha1($this->name.$this->game->getName().$this->toDate), 0, 11);
		foreach ($opt as $key=>$val) {
			$this->$key = $val;
		}
		if ($existingTournament) {
			$this->id = $data['to_id'];
		}
		else {
			//create sortingTable
			$this->createTable();
			//make entry in tournaments table and get the last insert id as the Tournament objects id property
			$this->id = $this->setTournamentData($data['to_options']);
			//populate sorting table with entered player data
			Player::setPlayers($this->sortingTable, $this->id, $data['players']);
			setcookie('to_id', $this->id, time() + 86400);
			setcookie('currentRound', 1, time() + 86400);
		}
		
	}
	private function getTournamentData($id) {
		$data = '';
		$sql = 'SELECT * FROM tournament WHERE to_id = :to_id';
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute(['to_id' => $id]);
		$data = $stmt->fetch();
		$data['to_options'] = unserialize($data['to_options']);
		return $data;
		
	}
	private function setTournamentData($options) {
		$options = serialize($options);
		$sql = 'INSERT INTO tournament (to_name, to_date, to_game, to_sortingTable, to_options) VALUES (:to_name, :to_date, :to_game, :to_sortingTable, :to_options)';
		$data = ['to_name' => $this->name,
				'to_date' => $this->toDate,
				'to_game' => $this->game->getId(),
				'to_sortingTable' => $this->sortingTable,
				'to_options' => $options];
		$stmt = self::dbConn()->prepare($sql);
		$stmt->execute($data);
		return self::dbConn()->lastInsertId();
	}
	//we create the table we will be using to sort players from best to worst using the given game's scoring columns
	private function createTable() {
		$columns = [];
		foreach ($this->game->getColumns() as $column) {
			$columns[] = $column.' INT(6) DEFAULT 0';
		}
		$sql = 'CREATE TABLE '.$this->sortingTable.'(
			cl_broj INT(3) UNSIGNED PRIMARY KEY,
			pl_name VARCHAR(30),
			pl_faction VARCHAR(30),
			to_id INT(6) UNSIGNED, '.implode(', ', $columns).'
			)';
		self::dbConn()->query($sql);
		
	}
	public function newRound () {
		$data = [];
		setcookie('currentRound', $this->optCurrentRound + 1, time() + 86400);
		$options = serialize(['optMaxRounds' => $this->optMaxRounds,
					'optCurrentRound' => $this->optCurrentRound + 1,
					'optPairingSystem' => $this->optPairingSystem,
					'optByeValues' => $this->optByeValues]);
		
		$sql = 'UPDATE tournament SET to_options = \''.$options.'\' WHERE to_id = '.$this->id;
		self::dbConn()->query($sql);
		$data['playerList'] = [];
		$columns = rtrim(implode (' DESC, ',$this->game->getColumns()) , ', ').' DESC';
		$sql = 'SELECT * FROM '.$this->sortingTable.' ORDER BY '.$columns;
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$data['playerList'][] = new Player($row);
		}
		$data['pairs'] = [];
		$sql = 'SELECT * FROM pairs WHERE to_id = '.$this->id;
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$data['pairs'][] =[$row['pl1_id'], $row['pl2_id']];
		}
		
		$pairingSystem = 'Classes\PairingSystems\\'.$this->optPairingSystem;
		$newPairs = $pairingSystem::getPairs($data);
		$newPairs = $this->getSortedPairs($newPairs);
		return $newPairs;
		
		
	}
	//update the player rankings table with data from the latest round
	public function updateRankings(array $rows) {
		foreach ($rows as $row) {
			$sql = 'UPDATE '.$this->sortingTable.' SET ';
			foreach ($row as $col => $val) {
				if ($col != 'cl_broj') {
					$sql .= " $col = $col + $val , ";
				}
			}
			$sql = rtrim($sql, ', ');
			$sql.= ' WHERE cl_broj = '.$row['cl_broj'];
			self::dbConn()->query($sql);
			
		}
		//if the game uses a Strength of Schedule column, calculate each players SoS and update rnakings
		if (in_array('SoS', $this->game->getColumns())) {
			foreach ($rows as $row) {
				$SoS = $this->calcSoS($row['cl_broj']);
				$sql = 'UPDATE '.$this->sortingTable.' SET SoS = '.$SoS.' WHERE cl_broj = '.$row['cl_broj'];
				self::dbConn()->query($sql);
			}
		}
	}
	//calculate the Strength of Schedule score for given player
	private function calcSoS($plId) {
		$winsCol = $this->game->getColumns()[0];
		$sql = 'SELECT pl1_id, pl2_id FROM pairs WHERE to_id = '.$this->id.' AND pl1_id = '.$plId.' OR pl2_id = '.$plId;
		$stmt = self::dbConn()->query($sql);
		$oponents = [];
		while($row = $stmt->fetch()) {
			$oponents[] = ($row['pl1_id'] == $plId) ? 'cl_broj = '.$row['pl2_id'] : 'cl_broj = '.$row['pl1_id'];
		}
		$oponents = implode(' OR ', $oponents);
		$sql = 'SELECT sum('.$winsCol.') SoS FROM '.$this->sortingTable.' WHERE '.$oponents.' GROUP BY to_id';
		$stmt = self::dbConn()->query($sql);
		$row = $stmt->fetch();
		return $row['SoS'];
	}
	//generate switchable dialog with random pairings for the first round
	public function getRandomPairs($players) {
		$shuffled = $players;
		shuffle($shuffled);
		list($pl1, $pl2) = array_chunk($shuffled, count($shuffled) /2);
		$left = '<ul class="playerSort" id="pl1">';
		$right = '<ul class="playerSort" id="pl2">';
		for ($i = 0; $i<count($pl1); $i++) {
			$left .= '<li id="'.$pl1[$i]['cl_broj'].'">'.$pl1[$i]['pl_name'].'</li>';
			$right .= '<li id="'.$pl2[$i]['cl_broj'].'">'.$pl2[$i]['pl_name'].'</li>';
		}
		$left .= '</ul>';
		$right .= '</ul>';
		$button = '<br><input type="submit" id="savePairs" value="SAČUVAJ PAROVE" class="formButton">';
		return '<div class="sortWrapper">'.$left.$right.'</div>'.$button;
	}
	//generate switchable dialog based on selected pairingSystem
	private function getSortedPairs ($pairs){
		$left = '<ul class="playerSort" id="pl1">';
		$right = '<ul class="playerSort" id="pl2">';
		for ($i = 0; $i<count($pairs); $i++) {
			$pl1 = $pairs[$i][0];
			$pl2 = $pairs[$i][1];
			$left .= '<li id="'.$pl1->getPlId().'">'.$pl1->getPlName().'</li>';
			$right .= '<li id="'.$pl2->getPlId().'">'.$pl2->getPlName().'</li>';
		}
		$left .= '</ul>';
		$right .= '</ul>';
		$button = '<br><input type="submit" id="savePairs" value="SAČUVAJ PAROVE" class="formButton">';
		return '<div class="sortWrapper">'.$left.$right.'</div>'.$button;
	}
	//get the final rankings table after tournament is finished
	public function getFinalRankings() {
		//get list of skins stylesheets
		$skins = scandir('css/finalSkins/');
		$skinSelector = '<br><br><span class="finalSkins">Skin: <select id="skinSelector">';
		unset($skins[0], $skins[1]);
		foreach($skins as $skin) {
			$selected = ($skin == 'default.css') ? 'selected' : '';
			$skinSelector .= '<option value="'.$skin.'"'.$selected.'>'.rtrim($skin, '.css').'</option>';
		}
		$skinSelector .= '</selector></span>';
		$table = '<div class="finalWrapper">';
		$table .= '<table class="finalTitleBar"><tr>';
		$table .= '<td class="logo"><img src="img/logo.png"></td>';
		$title = (isset($this->name) && $this->name != '') ? $this->name : $this->game->getName().' TURNIR '.date('j.n.Y.', strtotime($this->toDate));
		$table .= '<td class="title">'.$title.'</td>';
		$table .= '<td class="logo"><img src="'.$this->game->getImg().'"></td>';
		$table .= '</tr></table>';
		$columns = implode (', ', $this->game->getColumns());
		$colOrder = '';
		$colTable = '';
		foreach ($this->game->getColumns() as $column) {
			$colOrder .= $column.' DESC, ';
			$colTable .= "<td>$column</td>";
		}
		$colOrder = rtrim($colOrder, ', ');
		$sql = 'SELECT pl_name, pl_faction, '.$columns.' FROM '.$this->sortingTable.' ORDER BY '.$colOrder;
		$stmt = self::dbConn()->query($sql);
		$table .= '<table class="finalRankings">';
		$table .= '<tr class="frHeader"><td>#</td><td>Igrač</td>'.$colTable.'</tr>';
		$odd = true;
		$mesto = 1;
		while ($row = $stmt->fetch()) {
			if ($row['pl_name'] != 'BYE') {
				$rowClass = ($odd) ? 'odd' : 'even';
				$table .= '<tr class="'.$rowClass.'"><td>'.$mesto.'</td><td>'.$row['pl_name'].'<span class="frFaction">'.$row['pl_faction'].'</span></td>';
				foreach ($row as $key => $val) {
					if ($key != 'pl_name' && $key != 'pl_faction') {
						$table .= "<td>$val</td>";
					}
				}
				$table .= '</tr>';
				$odd = !$odd;
				$mesto++;
			}
		}
		$table .= '</table>'.$skinSelector.'</div>';
		return $table;
		
	}
	//get tournament header html
	public function getHeader() {
		$header = '<table class="tmHeader"><tr>';
		$title = (isset($this->name) && $this->name != '') ? $this->name : $this->game->getName().' TURNIR '.date('j.n.Y.', strtotime($this->toDate));
		$header .= '<td style="width:350px"><img src="img\logo.png"></td>';
		$header .= '<td class="title">'.$title.'</td>';
		$header .= '<td style="width:350px"><img src="'.$this->game->getImg().'">';
		$header .= '</tr></table>';
		return $header;
	}
	//generate html form to record player scores for the current round
	public function getRoundForm() {
		$pairs = Player::getCurrentPairs($this->optCurrentRound, $this->sortingTable, $this->id);
		$odd = true;
		//generate array of scoring columns and remove SoS column if present, since SoS values are calculated automatically
		$scoringColumns = $this->game->getColumns();
		if (($key = array_search('SoS', $scoringColumns)) !== false) {
			unset($scoringColumns[$key]);
		}
		$columnsHeader = '';
		$columnFields = '';
		$byeFields = '';
		foreach($scoringColumns as $col) {
			$columnsHeader .= "<th>$col</th>";
			$columnFields .= '<td><input type="text" class="columnField" data-column="'.$col.'" size="3"></td>';
			$byeFields .= '<td><input type="text" class="columnField" data-column="'.$col.'" size="3" 
							value="'.$this->optByeValues[$col].'"></td>';
		}
		$vsTable = '<table class="roundForm"><tr></tr>';
		$pl1Table = '<table class="roundForm"><tr><th>Igrač</th>.'.$columnsHeader.'</tr>';
		$pl2Table = '<table class="roundForm"><tr><th>Igrač</th>.'.$columnsHeader.'</tr>';
		foreach($pairs as $pair) {
			$rowClass = ($odd) ? 'odd' : 'even';
			//check to see if there is a BYE player in the pair and supply preset bye values to unmatched player
			if ($pair['pl1']->getPlId() == 999 || $pair['pl2']->getPlId() == 999) {
				if ($pair['pl1']->getPlId() == 999) {
					$pl1Table.= '<tr class="'.$rowClass.'"><td style="text-align: center;" colspan="12">BYE</td></tr>';
					$pl2Table .= '<tr class="playerResult '.$rowClass.'" data-plId="'.$pair['pl2']->getPlId().'">
								<td>'.$pair['pl2']->getPlName().'</td>'.$byeFields.'</tr>';
				}else {
					$pl1Table .= '<tr class="playerResult '.$rowClass.'" data-plId="'.$pair['pl1']->getPlId().'">
								<td>'.$pair['pl1']->getPlName().'</td>'.$byeFields.'</tr>';
					$pl2Table.= '<tr class="'.$rowClass.'"><td style="text-align: center;" colspan="12">BYE</td></tr>';
				}
				
			} else {
				$pl1Table .= '<tr class="playerResult '.$rowClass.'" data-plId="'.$pair['pl1']->getPlId().'">
							<td>'.$pair['pl1']->getPlName().'</td>'.$columnFields.'</tr>';
				$pl2Table .= '<tr class="playerResult '.$rowClass.'" data-plId="'.$pair['pl2']->getPlId().'">
							<td>'.$pair['pl2']->getPlName().'</td>'.$columnFields.'</tr>';
			}
			$vsTable .= '<tr class="'.$rowClass.'"><td>VS</td></tr>';
			$odd = !$odd;
		}
		$vsTable .= '</table>';
		$pl1Table .= '</table>';
		$pl2Table .= '</table>';
		$saveRoundButton = '<br><input type="submit" class="formButton" id="saveRound" value ="SAČUVAJ RUNDU">';
		$finishTournamentButton = '<br><input type="submit" class="formButton" id="saveRound" value ="ZAVRŠI TURNIR">';
		$button = ($this->optCurrentRound == $this->optMaxRounds) ? $finishTournamentButton : $saveRoundButton;
		return '<div class="roundFormWrapper">'.$pl1Table.$vsTable.$pl2Table.$button.'</div>';
	}
	//delete all relevant cookies and DB data once tournament is finished
	public function clearTournament() {
		$sql = 'DROP TABLE '.$this->sortingTable.'; ';
		$sql .= 'DELETE FROM pairs WHERE to_id = '.$this->id.';';
		$sql .= 'DELETE FROM tournament WHERE to_id = '.$this->id.';';
		self::dbConn()->query($sql);
	}
}
?>