<?php

declare(ticks = 1);

class Cli_script {
	
	public function __construct() {
		pcntl_signal(SIGTERM, array($this, 'quit'));
		pcntl_signal(SIGINT, array($this, 'quit'));
		set_error_handler(array($this, 'on_error'));
	}

	public function quit($code = 0) {
		exit($code);
	}

	public function prompt($msg = "\r\nPress enter to continue\r\n") {
		echo $msg;
		$fh = fopen("php://stdin", "r"); 
		fgets( $fh); 
		fclose($fh);
	}

	public function on_error($errno , $errstr, $errfile, $errline) {
		echo "ERROR #$errno: $errstr on $errfile line $errline";
	}


}


?>