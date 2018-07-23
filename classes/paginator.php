<?php
	namespace Classes;
	class Paginator extends AbstractBase{
	public $numrows;
	public $pagesize;
	public $rangelen;
	public $range;
	public $pageqstr;
	public $errors;
	public $currentpage;
	private $numpages;
	public $prevstr;
	public $nextstr;
	public $cleanquerystr;
	
	
		public function __construct() {
		//podesiti pocetne vrednosti
		$this->pagesize = 10;
		$this->rangelen = 7;
		$this->pageqstr = (isset($_GET['page'])) ? 'page' : '';
		$this->numrows = 0;
		$this->range = Array();
		$this->errors = Array();
		$this->currentpage = 0;
		$this->prevstr = '&lt;&lt;&lt;';
		$this->nextstr = '&gt;&gt;&gt;';
		}
		public function setPageSize($pagesize) {
			if ($pagesize == '' || !is_int($pagesize)) {
			$errors[] = '<span class="error">Velicina stranice je nepravilna, upotrebljena je pocetna vrednost od '.$this->pagesize.'</span>';
			}
			else {
			$this->pagesize = $pagesize;
			}
		}
		public function setrangelen($rangelen) {
			if ($rangelen == '' || !is_int($rangelen)) {
			$errors[] = '<span class="error">Opseg stranica je nepravilan, upotrebljena je pocetna vrednost od '.$this->rangelen.'</span>';
			}
			else {
			$this->rangelen = $rangelen;
			}
		}
		public function setPageQstr($qstring) {
		$this->pageqstr = $qstring;
		}
		public function setNumRows($numrows) {
			if ($numrows > 1 || !is_int($numrows)) {
			$errors[] = '<span class="error">Broj redova je nepravilan, morate uneti broj veci od 1</span>';
			}
			else {
			$this->numrows = $numrows;
			}
		$this->setNumPages();
		}
		public function getNumRows ($table) {
			
			$sql = 'SELECT count(*) as numrows FROM '.$table;
			$stmt = self::dbConn()->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch();
			$this->numrows = $row['numrows'];
			$this->setNumPages();
		}
		private function setNumPages() {
		$this->numpages = ceil($this->numrows / $this->pagesize);
		}
		public function getLimit() {
			$offset = ($_GET[$this->pageqstr] != '' && $_GET[$this->pageqstr] > 0) ? $_GET[$this->pageqstr] * $this->pagesize.', ' : '';
			return $offset.$this->pagesize;
		}
		private function getCurrentPage() {
		$this->currentpage = (isset($_GET[$this->pageqstr]) && $_GET[$this->pageqstr] != '') ? ceil($_GET[$this->pageqstr]) : 1;
		}
		private function snipQueryString() {
			
			if ($_SERVER['QUERY_STRING'] == '') {
			$this->cleanquerystr = $_SERVER['PHP_SELF'];
			}
			else {
			$remove = array($this->pageqstr.'='.$this->currentpage, 
							$this->pageqstr.'='.$this->currentpage.'&',
							'&'.$this->pageqstr.'='.$this->currentpage);
			
			
			$sanspage = str_replace($remove, '', $_SERVER['QUERY_STRING']);
			
			
			//formatiranje qstringa
				$sanspage = ltrim(rtrim($sanspage, '&'), '&');
				//$sanspage = preg_replace('/^\&/', '', $sanspage);
				
				//$sanspage = preg_replace('/\&$/', '', $sanspage);
			$this->cleanquerystr = ($sanspage == '') ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'].'?'.$sanspage;
			}
		
		}
		private function calcRanges() {
			$this->range['start'] = $this->currentpage - (floor($this->rangelen/2));
			
			$this->range['end'] = $this->currentpage + (floor($this->rangelen/2));
				if ($this->range['start'] <= 0) {
				$this->range['end'] += abs($this->range['start']) +1;
				$this->range['start'] = 1;
				}
				if ($this->range['end'] > $this->numpages) {
				$this->range['start'] -= $this->range['end'] - $this->numpages;
				$this->range['end'] = $this->numpages;
				}
				if ($this->range['start'] < 1) {
					$this->range['start'] = 1;
				}
		}
		public function paginate() {
			$this->getCurrentPage();
			$this->calcRanges();
			
				if ($this->rangelen > $this->numpages) {
					$this->rangelen = $this->numpages;
				}
			//izbacivanje pageqstr vrednosti iz query stringa
			$this->snipQueryString();
			//provera obaveznih podtaka
			if ($this->numrows == '' || $this->numrows < 1) {
			$this->errors[] = '<span class="error">Broj rezultata za prikazivanje je nepravilan!</span>';
			}
			if (trim($this->cleanquerystr) == '') {
			$this->errors[] = '<span class="error">Nije uspelo ciscenje QS-a.</span>';
			}
			if (!empty($this->errors)) {
				foreach ($this->errors as $value) {
				echo $value;
				}
			}
			else {
			//strelica a nazad
				echo '<div class="pagelinkWrapper">';
				$back = $this->currentpage -1;
				$connect = (strpos($this->cleanquerystr, '?') == false) ? '?' : '&';
				$prevpage =($this->cleanquerystr == $_SERVER['PHP_SELF']) ? $this->cleanquerystr.'?'.$this->pageqstr.'='.$back : $this->cleanquerystr.$connect.$this->pageqstr.'='.$back;
				echo ($this->currentpage == 1) ? '<span class="disabledButton nolink">'.$this->prevstr.'</span>' : '<span class="currentPage"><a class="pagelink whitelink" href="'.$prevpage.'">'.$this->prevstr.'</a></span>';
				
					for ($i = 1; $i <= $this->numpages; $i++) {
					echo ($this->range['start'] > 1 && $i == $this->range['start']) ? '...' : '';
						if ($i >= $this->range['start'] && $i <= $this->range['end']) {
						echo ($i == $this->currentpage) ? '<span class="currentPage">'.$i.'</span>' : '<a class="pagelink" href="'.$this->cleanquerystr.$connect.$this->pageqstr.'='.$i.'">'.$i.'</a>';
						}
					echo ($this->range['end'] < $this->numpages  && $this->range['end'] == $i) ? '...' : '';
					}
				
				$fwd = $this->currentpage +1;
				$nextpage = $prevpage =($this->cleanquerystr == $_SERVER['PHP_SELF']) ? $this->cleanquerystr.$connect.$this->pageqstr.'='.$fwd : $this->cleanquerystr.'&'.$this->pageqstr.'='.$fwd;
				echo ($this->currentpage == $this->numpages) ? '<span class="disabledButton nolink">'.$this->nextstr.'</span>' : '<span class="currentPage"><a class="pagelink whitelink" href="'.$nextpage.'">'.$this->nextstr.'</a></span>';
			echo '</div>';
			}
		
		}
	}
?>