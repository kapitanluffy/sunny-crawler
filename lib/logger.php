<?php

class Logger {
	
	public $lines = 0;
	public $contents = array();
	public $maxlines = 1000;

	public function __construct($file) {
		$this->file = $file;
		$this->open();
		if(is_resource($this->fh)) $this->read();
	}

	public function set_maxlines($number) {
		$this->maxlines = $number;
	}

	public function open($mode = 'a+') {
		$this->fh = fopen($this->file, $mode);
		fwrite($this->fh, "\r\n");
		return $this->fh;
	}

	public function rewrite() {
		$this->close();
		$this->open('w+');
		$this->lines = 0;
	}

	public function write($string) {
		if(!is_resource($this->fh)) return false;

		if($this->lines >= $this->maxlines) $this->rewrite();

		$string = trim($string, "\r\n");
		$timestamp = date('Y-m-d H:i:s');
		$is_written = fwrite($this->fh, "$timestamp: $string\r\n");

		if($is_written) {
			$this->contents[] = "$timestamp: $string";
			$this->lines++;
			return "$timestamp: $string";
		}

		return false;
	}

	public function read() {
		if(!is_resource($this->fh)) return false;
		while(!feof($this->fh)){
			$line = fgets($this->fh);
			if(empty($line)) break;
			$this->contents[] = trim($line, "\r\n");
			$this->lines++;
		}

		return $this->contents;
	}

	public function close() {
		if(is_resource($this->fh)) fclose($this->fh);
	}

	public function __destruct() {
		if(is_resource($this->fh)) fclose($this->fh);
	}
}

?>