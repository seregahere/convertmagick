<?php

class ConvertMagick implements IConvertMagick {


	const COMPRESSION_JPEG = 'JPEG';
	const DEFAULT_QUALITY = 90;

	protected $filename = null;
	protected $image = null;
	private $iteratorIndex = null;
	private $imageFormat = 'JPG';
	private $geometry;
	private $compression;
	private $quality;
	private $convert='/usr/bin/convert';
	private $deletionQueue = array();

	private $mimeCache = array();

	public function __construct($filename = null) {
		
		$this->readImage($filename);		
	}

	public function __destruct() {
		$this->deleteQueue();
	}

	public function readImage ( $filename ) {
		$this->_log('readImage('.$filename.') started');
		$this->clear();
		if (strlen($filename) && file_exists($filename) && $this->isImageFile($filename)) {
			$res =  $this->getFormatByMIME( $this->getMIMEType($filename) );
			$this->_log('readImage: mime='.$res);
			$this->imageFormat = (strlen($res)>0 ? $res : 'jpg');

			$tmpCopy = $this->getTempFile();
			if (copy($filename, $tmpCopy)) {
				$this->filename = $tmpCopy;
				//image format
				$this->geometry = $this->getCurrentGeometry();
				
			}
		}
	}


	public function clear() {
		$this->_log('clear() started');
		if (strlen($this->filename) && file_exists($this->filename)) $this->addToDeletionQueue($this->filename);
		$this->deleteQueue();
		$this->filename = null;
		$this->image = null;
		$this->imageFormat = 'JPG';
		$this->iteratorIndex = 0;
		$this->geometry = array('width' => 0, 'height' => 0);
		$this->compression = self::COMPRESSION_JPEG;
		$this->quality = self::DEFAULT_QUALITY;

		return true;
	}

	public function destroy() {
		return $this->clear();
	}
	
	public function valid(){
		$this->_log('valid called');
		return $this->isImageFile($this->filename);
	}

	public function setIteratorIndex($index) {
		$this->iteratorIndex = $index;
	}

	public function getImageFormat() {
		return $this->imageFormat;
	}

	public function setImageFormat($format) {
		$this->_log('setImageFormat("'.$format.'") called');
		$format = strtoupper($format);
		if ($this->isValidFormat($format)) {
			 $this->imageFormat = $format;
		}

		return true;
	}

	public function getImageGeometry() {
		$this->_log('getImageGeometry() called');
		$this->_log($this->geometry);

		return $this->geometry;
	}

	public function setImageCompression($compression){
		$this->_log('setImageCompression("'.$compression.'") called');
		$this->compression = $compression;

		return true;
	}

	public function setImageCompressionQuality($quality) {
		$this->_log('setImageCompressionQuality("'.$quality.'") called');
		$this->quality = $quality;

		return true;
	}

	public function getImage() {
		$this->_log('getImage() called');
		$res = new ConvertMagick($this->filename);
		$res->setImageCompression($this->compression);
		$res->setImageFormat($this->imageFormat);
		$res->setIteratorIndex( $this->iteratorIndex);
		$res->setImageCompressionQuality($this->quality);

		return $res;
	}

	public function scaleImage($cols, $rows, $bestfit = false) {
		$this->_log(sprintf('scaleImage(%d, %d, %s) called', $cols, $rows, $bestfit ? 'true' : 'false'));
		$newFile = $this->getTempFile();
		$cmd = $this->convert.' "'.$this->filename.'" -resize "'
				.($cols>0 ? $cols : '')
				.'x'
				.($rows>0 ? $rows : '')
				.'" '
				.' -quality '.$this->quality
				.' -compress '.$this->compression
				.' "'.$newFile.'"';

		return $this->executeChangeImageCommand($cmd, $newFile);
/*
		$stdout = $this->_execute($cmd);

		if (preg_match('/error/i', $stdout)) {
			throw new Exception($stdout);
		} else {
			$this->addToDeletionQueue($this->filename);
			$this->filename = $newFile;
			$this->geometry = $this->getCurrentGeometry();
		}
		
		return true;
*/
	}
	
	public function cropImage($width, $height, $x, $y) {
		$this->_log(sprintf('cropImage(%d, %d, %d, %d) called', $width, $height, $x, $y));
		$newFile = $this->getTempFile();
		$cmd = $this->convert.' "'.$this->filename.'" -crop "'
				.($width>0 ? $width : '')
				.'x'
				.($height>0 ? $height : '')
				.'+'.sprintf('%d', $x).'+'.sprintf('%d', $y)
				.'" '
				.' -quality '.$this->quality
				.' -compress '.$this->compression
				.' "'.$newFile.'"';

		return $this->executeChangeImageCommand($cmd, $newFile);
/*
		$stdout = $this->_execute($cmd);

		if (preg_match('/error/i', $stdout)) {
			throw new Exception($stdout);
		} else {
			$this->addToDeletionQueue($this->filename);
			$this->filename = $newFile;
			$this->geometry = $this->getCurrentGeometry();
		}
		
		return true;
*/
	}

	public function setImagePage($width, $height, $x, $y) {
		$this->_log(sprintf('setImagePage(%d, %d, %d, %d) called', $width, $height, $x, $y));
		$this->_log($this->geometry);
		if (
			$this->geometry['width'] != $width
			|| $this->geometry['height'] != $height
			|| $x>0 || $y>0
		) {

			$newFile = $this->getTempFile();
			$cmd = $this->convert.' "'.$this->filename.'" -repage "'
					.($width>0 ? $width : '')
					.'x'
					.($height>0 ? $height : '')
					.'+'.sprintf('%d', $x).'+'.sprintf('%d', $y)
					.'" '
					.' "'.$newFile.'"';
			return $this->executeChangeImageCommand($cmd, $newFile);
/*
			$stdout = $this->_execute($cmd);

			if (preg_match('/error/i', $stdout)) {
				throw new Exception($stdout);
			} else {
				$this->addToDeletionQueue($this->filename);
				$this->filename = $newFile;
				$this->geometry = $this->getCurrentGeometry();
			}
*/
		}

		return true;
	}

	public function rotateImage($background, $degrees) {
		$this->_log(sprintf('rotateImage("%s", %f) called', $background, $degrees));
		$newFile = $this->getTempFile();
			$cmd = $this->convert.' "'.$this->filename.'" -rotate '.$degrees
				.' "'.$newFile.'"';

		return $this->executeChangeImageCommand($cmd, $newFile);
/*
			$stdout = $this->_execute($cmd);

			if (preg_match('/error/i', $stdout)) {
				throw new Exception($stdout);
			} else {
				$this->addToDeletionQueue($this->filename);
				$this->filename = $newFile;
				$this->geometry = $this->getCurrentGeometry();
			}
*/
	}


	public function flipImage() {
		$this->_log('flipImage() called');
		$newFile = $this->getTempFile();
		$cmd = $this->convert . ' "' . $this->filename . '" -flip  "' . $newFile . '"';

		return $this->executeChangeImageCommand($cmd, $newFile);
	}

	public function flopImage() {
		$this->_log('flopImage() called');
		$newFile = $this->getTempFile();
		$cmd = $this->convert . ' "' . $this->filename . '" -flop  "' . $newFile . '"';

		return $this->executeChangeImageCommand($cmd, $newFile);
	}

	public function getImageBlob() {
		return file_get_contents($this->filename);
	}

	public function	writeImage($filename) {
		$this->_log(sprintf('writeImage("%s") called', $filename));
		$cmd = $this->convert.' "'.$this->filename.'" "'.$filename.'"';
		$stdout = $this->_execute($cmd);

		return (!preg_match('/error/i', $stdout));
	}

/* PRIVATE METHODS */

	private function executeChangeImageCommand($cmd, $newFile) {
		$stdout = $this->_execute($cmd);
		$res = true;
		if (preg_match('/error/i', $stdout)) {
				$res = false;
				throw new Exception($stdout);
		} else {
				$this->addToDeletionQueue($this->filename);
				$this->filename = $newFile;
				$this->geometry = $this->getCurrentGeometry();
				
		}

		return $res;
	}


	private function isValidFormat($format) {
		return in_array($format, array('JPG', 'PNG', 'GIF'));
	}


	private function getFormatByMIME($mimetype) {
		$res = preg_match('/image/i', $mimetype) ? strtoupper(str_replace("image/", "", $mimetype)) : null;
		if ($res === 'JPEG') $res = 'JPG';

		return $res;
	}

	private function isImageFile($filename){
		$this->_log('isImageFile('.$filename.') started');
		return preg_match('/image/i', $this->getMIMEType($filename));
	}

	private function getMIMEType($filename) {
		$this->_log('getMIMEType('.$filename.') started');
		$mimetype = '';
		
		if (strlen($filename) && file_exists($filename)) {

			if (array_key_exists($filename, $this->mimeCache)) {
				$mimetype = $this->mimeCache[$filename];
			} else {
				if(function_exists('mime_content_type')) {
					$this->_log('[mime_content_type] checking '.$filename);
					$mimetype = mime_content_type($filename);
					$this->_log('[mime_content_type] result: '.$mimetype);
				} elseif (function_exists('exif_imagetype')) {
				   $mimetype = $this->getMIMEbyEXIF($filename);
				} else {
					die('no methods to validate!');
				}

				if (strlen($mimetype)) $this->mimeCache[$filename] = $mimetype;
			}
		}

		$this->_log('getMIMEType result: '.$mimetype);
		return $mimetype;
	}

	private function getMIMEbyEXIF($filename) {
		$this->_log('[exif_imagetype] checking '.$filename);
		$byte = exif_imagetype($filename);
		$res = 'application/octet-stream';
		switch ($byte) {
			case IMAGETYPE_GIF: $res = 'image/gif'; break;
			case IMAGETYPE_JPEG: $res = 'image/jpg'; break;
			case IMAGETYPE_PNG: $res = 'image/png'; break;
		}

		$this->_log('[exif_imagetype] result: '.$res);
		return $res;
	}

	private function getTempFile(){
		return tempnam(sys_get_temp_dir(), 'ConvertMagick').'.'.strtolower($this->imageFormat);
	}

	private function _log($log) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG )  {
			if ( function_exists('write_log')) {
				write_log ( $log );
			} else {
				  if ( is_array( $log ) || is_object( $log ) ) {
					 error_log( print_r( $log, true ) );
				  } else {
					 error_log( $log );
				  }
			}
		}
	}

	private function _execute($cmd) {
		$this->_log($cmd);

		return exec($cmd);
	}

	private function dumpCurrentImage(){
		$tempFile = $this->getTempFile();
		file_put_contents($tempFile, $this->image);

		return $tempFile;
	}

	private function getCurrentGeometry() {
		$this->_log('private getCurrentGeometry() called for '.$this->filename);
		$res = array('width'=>0, 'height'=>0);
		$cmd = $this->_execute($this->convert.' "'.$this->filename.'" -print "%w %h" /dev/null');
		if (preg_match('/(\d+)\s+(\d+)/', $cmd)) {
			$sizes = explode(' ', $cmd);
			$res['width'] = $sizes[0];
			$res['height'] = $sizes[1];
		}
		$this->_log($res);

		return $res;
	}
		
	private function addToDeletionQueue($filename) {
		$this->deletionQueue[$filename] = 1;
	}

	private function deleteQueue(){
		foreach ($this->deletionQueue as $filename=>$v) {
			$this->_log('delete '.$filename);
			unlink($filename);
		}
		$this->deletionQueue = array();
	}
}
