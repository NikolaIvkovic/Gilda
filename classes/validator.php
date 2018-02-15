<?php
namespace Classes;
class Validator {
public $fields;
public $errors;
private $valresult;
private $imageformats;

public function __construct() {

$this->fields = array();
$this->valresult = array();
$this->errors=array();
$this->imageformats = array('image/png', 'image/jpeg', 'image/gif');
}

public function addField($source,$label, $type, $widthHeight = NULL, $regex = NULL, $errmsg = NULL) {
	if ($type == 'custom') {
	$this->fields[] = array('source' => $source,'label' => $label,'type' => $type, 'regex'=>$regex, 'errmsg'=>$errmsg);	
	}
	Else{
	$this->fields[] = array('source' => $source,'label' => $label,'type' => $type, 'widthHeight' =>$widthHeight, 'regex'=>'', 'errmsg'=>'');
	}
}
public function pljuni() {
return $this->fields;
}
public function validate() {
	foreach ($this->fields as $value) {
	switch ($value['type']) {
	case 'num': 
	if (is_numeric($value['source'])) {
	$this->valresult[] = true;
	}
	else {
	$this->errors[] = $value['label'].' mora da bude numericka vrednost!';
	}
	break;
	case 'string':
	if (preg_match('@[A-Z]|[a-z]@', $value['source'])) {
	$this->valresult[] = true;
	}
	else {
	$this->errors[] = $value['label'].' mora da bude tekst!';
	}
	break;
	case 'email':
	if (filter_var($value['source'], FILTER_VALIDATE_EMAIL)) {
	$this->valresult[] = true;
	}
	else {
	$this->errors[] = $value['label'].' mora biti email!';
	}
	break;
	case 'date':
	if(preg_match('@^\d{2}.\d{2}.\d{4}$@', $value['source'])) {
	$this->valresult[] = true;
	}
	else{
	$this->errors[] = $value['label'].' mora biti pravilan DD.MM.YYYY datum! (npr prvi maj 2016 je 01.05.2016)';
	}
	break;
	case 'custom' :
	if (preg_match($value['regex'], $value['source'])) {
	$this->valresult[] = true;
	}
	else{
	$this->errors[] = $value['label'].$value['errmsg'];
	}
	case 'url' : 
	if (!preg_match('@^http://.*@', $value['source'])) {
	$value['source'] = 'http://'.$value['source'];
	}
	if (filter_var($value['source'], FILTER_VALIDATE_URL)) {
	$this->valresult[] = true;
	}
	else{
	$this->errors[] = $value['label'].' mora biti validan URL';
	}
	break;
	case 'req' :
	if (trim($value['source']) != '') {
	$this->valresult[] = true;
	}
	else{
	$this->errors[] = $value['label'].' je obavezno polje';
	}
	break;
	case 'img':
	$formats = '';
	foreach ($this->imageformats as $form) {
		$formats .= substr($form, 6).', ';
	}
	$formats = substr($formats, 0, -2);
	
	if (!in_array($value['source']['type'], $this->imageformats)) {
		$this->errors[] = $value['label']. ' mora biti jedan od sledecih formata: '.$formats;
	}
	foreach($value['widthHeight'] as $size) {
		if (!isset($maxWidth)) {
			$maxWidth = $size;
		}
		else {
			$maxHeight = $size;
		}
	}
	$imgsize = getimagesize($value['source']['tmp_name']);
	if ($imgsize [0] > $maxWidth) {
		$this->errors[] = 'Širina '.$value['label'].' ne sme biti veća od '.$maxWidth;
	}
	if ($imgsize [1] > $maxHeight) {
		$this->errors[] = 'Visina '.$value['label'].' ne sme biti veća od '.$maxHeight;
	}
	break;
	}
}
if(empty($this->errors) && count(array_unique($this->valresult)) == 1) {
return true;
}
else {
$_SESSION['errors']['validation'] = $this->errors;
}

}
public function printError(){
return $this->errors;
}
}



?>