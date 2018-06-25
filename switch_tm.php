<?php
include 'autoloader.php';

switch ($_REQUEST['action']) {
	case 'getGamesAndSystems':
		$data = [];
		$columns = [];
		$games = '';
		$list = Classes\Game::getGameList();
		foreach ($list as $game) {
			$games .= '<option value="'.$game['gm_id'].'">'.$game['gm_name'].'</option>';
			$colArr = unserialize($game['gm_columns']);
			$columns[$game['gm_id']] = '';
			foreach ($colArr as $col) {
				$columns[$game['gm_id']] .= $col.': <input type="text" size="5" id="'.$col.'"> ';
			}
		}
		$data['games'] = $games;
		$data['columns'] = $columns;
		$files = scandir(__DIR__.'/Classes/PairingSystems');
		$ignore = ['.', '..', 'PairingSystem.php'];
		$systems = '';
		foreach ($files as $system) {
			if (!in_array($system, $ignore)) {
				$system = substr($system, 0, -4);
				$systems .= '<option value="'.$system.'">'.$system.'</option>';
			} 
		}
		$data['systems'] = $systems;
		echo json_encode($data);
	break;
	case 'createTournament':
		$tournament = new Classes\Tournament ($_REQUEST['data']);
		$data['to_id'] = $tournament->getId();
		$data['randomPairs'] = $tournament->getRandomPairs($_REQUEST['data']['players']);
		echo json_encode($data);
	break;
	case 'savePairs':
		Classes\Player::savePairs($_REQUEST['pairs']);
	break;
	case 'getRound':
		$tournament = new Classes\Tournament ($_REQUEST['data']);
		echo $tournament->getHeader();
		echo Classes\Player::getPairs($_REQUEST['data']['to_id'], $tournament->getSortingTable(), $tournament->getId());
		echo $tournament->getRoundForm();		
	break;
	case 'saveRound':
		$tournament = new Classes\Tournament (['to_id' =>$_REQUEST['to_id']]);
		$tournament->updateRankings($_REQUEST['rows']);
		echo $tournament->newRound();
	break;
	case 'finishTournament':
		$tournament = new Classes\Tournament (['to_id' =>$_REQUEST['to_id']]);
		Classes\Player::updateRankings($tournament);
		echo $tournament->getFinalRankings();
		$tournament->clearTournament();
		unset($_COOKIE['to_id']);
		setcookie ('to_id', '', time() -100);
		unset ($_COOKIE['currentRound']);
		setcookie('currentRound', '', time() -100);
	break;
	case 'test':
		$tournament = new Classes\Tournament(['to_id' => 1]);
		echo $tournament->newRound();
	break;

}
?>