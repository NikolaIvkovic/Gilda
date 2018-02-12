<?php
class errorHandler {
	private $system;
	private $validation;
	private $notices;
	private static $instance = null;
	private $renderWrapper;
	
	private function __construct() {
		$this->system = (isset($_SESSION['errors']['system'])) ? $_SESSION['errors']['system'] : array();
		$this->validation = (isset($_SESSION['errors']['validation'])) ? $_SESSION['errors']['validation'] : array();
		$this->notices = (isset($_SESSION['errors']['notices'])) ? $_SESSION['errors']['notices'] : array();
		$this->renderWrapper = (isset ($_SESSION['errors']) && count($_SESSION['errors']) > 0) ? true : false;
		unset($_SESSION['errors']);
	}
	public static function load () {
		if (self::$instance == null) {
			self::$instance = new errorhandler();
		}
		return self::$instance;
	}
	public function listErrors () {
		echo ($this->renderWrapper) ? '<div id="errWrapper">' : '';
		if (count($this->system) > 0) {
			foreach ($this->system as $e) {
				echo '<div class="errSystem"><b>Error '.$e->getCode().':</b> '.$e->getMessage().'<br>
						in '.$e->getFile().' on line '.$e->getLine().'</div>';
			}
		}
		if (count($this->validation) > 0) {
			foreach($this->validation as $e) {
				echo '<div class="errValidation">'.$e.'</div>';
			}
		}
		if (count($this->notices) > 0) {
			foreach($this->notices as $e) {
				echo '<div class="errNotice">'.$e.'</div>';
			}
		}
	echo ($this->renderWrapper) ? '</div>' : '';
	}
}
?>