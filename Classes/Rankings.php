<?php
namespace Classes;
class Rankings extends AbstractBase {
	
	public static function getGames() {
		$games = '<ul class="gamelist">';
		$sql = 'SELECT gm_id, gm_img FROM game';
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$games .= '<li data-gm_id="'.$row['gm_id'].'"><img src="img/games/'.$row['gm_img'].'"></li>';
		}
		$games .= '</ul>';
		return $games;
	}
	public static function getRankings($gm_id) {
		$data = [];
		$columns = '';
		$sql = 'SELECT c.cl_imeprezime, r.rnk_scores, r.rnk_tournamentsWon FROM
				rankings r JOIN gildadb.clanovi c ON
				r.cl_broj = c.cl_broj 
				WHERE gm_id = '.$gm_id.' AND rnk_year = '.date('Y');
		$stmt = self::dbConn()->query($sql);
		while ($row = $stmt->fetch()) {
			$player = ['cl_imeprezime' => $row['cl_imeprezime'],
						'rnk_tournamentsWon' => $row['rnk_tournamentsWon']];
			$scores = unserialize($row['rnk_scores']);
			$columns = ($columns == '') ? array_keys($scores) : $columns;
			foreach ($scores as $key=>$val) {
				$player[$key] = $val;
			}
			$data[] = $player;	
		}
		$columnsTH = '';
		foreach ($columns as $col) {
			$columnsTH.= "<th>$col</th>";
		}
		$multisortStr = 'array_multisort(array_column($data, "rnk_tournamentsWon"), SORT_DESC, ';
		foreach($columns as $col) {
			$multisortStr .= 'array_column($data, "'.$col.'"), SORT_DESC, ';
		}
		$multisortStr.= '$data);';
		eval($multisortStr);
		$table = '<table class="yearlyRankings">
					<tr><th>Ime i Prezime</th><th>Osvojeni<br>Turniri</th>'.$columnsTH.'</tr>';
		$odd = true;
		foreach ($data as $row) {
			$scoreTD = '';
			foreach($columns as $col) {
				$scoreTD .= '<td class="num">'.$row[$col].'</td>';
			}
			$rowClass = ($odd) ? 'rankOdd' : 'rankEven';
			$table .= '<tr class="'.$rowClass.'">
						<td>'.$row['cl_imeprezime'].'</td>
						<td class="num">'.$row['rnk_tournamentsWon'].'</td>'.$scoreTD.'</tr>';
			$odd = !$odd;
		}
		$table .= '</table>';
		return $table;
	}
}
?>