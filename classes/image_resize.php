<?php
namespace Classes;
class slika{
	private $file;
	private $target_filename;
	private $origWidth;
	private $origHeight;
	private $destWidth;
	private $destHeight;
	
	public function __construct($file, $tilesize = 125) {
		$this->file = $file['tmp_name'];
		$sizes = getimagesize($this->file);
		$this->origWidth = $sizes[0];
		$this->origHeight = $sizes[1];
		$ratio = $this->origWidth / $this->origHeight;
		if ($ratio > 1) {
			$this->destWidth = $tilesize;
			$this->destHeight = $tilesize / $ratio;
		}
		else {
			$this->destWidth = $tilesize * $ratio;
			$this->destHeight = $tilesize;
		}
		
	}
	private function setFilename() {
		$filename = md5($_POST['art_naziv']);
		
		$this->target_filename = 'img/artikli/'.$filename.'.png';
	}
	public function getFilename() {
		return $this->target_filename;
	}
	public function saveResized() {
		$this->setFilename();
		$src = imagecreatefromstring(file_get_contents($this->file));
		$dst = imagecreatetruecolor($this->destWidth, $this->destHeight);
		 imagealphablending($dst, false);
		imagesavealpha($dst,true);
		$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
		imagefilledrectangle($dst, 0, 0, $this->destWidth, $this->destHeight, $transparent);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $this->destWidth, $this->destHeight, $this->origWidth, $this->origHeight);
		imagedestroy($src);
		imagepng($dst, APP_DIR.$this->target_filename);
		imagedestroy($dst);
	}
}


/*$pic = new slika($_FILES['slika']);
$pic->setFilename('gica');
$pic->saveResized();*/
?>