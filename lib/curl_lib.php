<?php

class Curl_lib {
	
	public $multi_handle;
	public $curls = array();
	public $response = array();

	function multi_get($options)	{
		$this->multi_handle = curl_multi_init();

		$defaults = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
		);

		$threads = count($options) - 1;
		for($i=0; $i<= $threads; $i++) {
			if(! @$options[$i][CURLOPT_URL]) {
				echo "Set CURLOPT_URL option\r\n";
				die;
			}

			foreach ($options[$i] as $option => $value) {
				$defaults[$option] = $value;
			}

			$this->curls[$i] = curl_init();
			curl_setopt_array($this->curls[$i], $defaults);
			curl_multi_add_handle($this->multi_handle, $this->curls[$i]);
		}

		do {
			$return = curl_multi_exec($this->multi_handle, $active);
		} while ($return == CURLM_CALL_MULTI_PERFORM);

		while ($active && $return == CURLM_OK) {
			$ready = curl_multi_select($this->multi_handle);
			if ($ready != -1) {
				do {
					$return = curl_multi_exec($this->multi_handle, $active);
				} while ($return == CURLM_CALL_MULTI_PERFORM);
			}
		}

		if ($return != CURLM_OK) {
			trigger_error("Curl multi read error $return\n", E_USER_WARNING);
		}

		for($i=0; $i<= $threads; $i++) {

			$error = curl_error($this->curls[$i]);

			if($error == "") {
				$this->response[$i] = curl_multi_getcontent($this->curls[$i]);
			} else {
				trigger_error("Curl error on handle $i: $error\n", E_USER_WARNING);
			}

			curl_multi_remove_handle($this->multi_handle, $this->curls[$i]);
			curl_close($this->curls[$i]);
		}
		curl_multi_close($this->multi_handle);
		return $this->response;
	}

	function get($url, $options = array(), $timeout = 30) {
		$this->curl = curl_init();
		$dflt_options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => $timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER =>1,
		);

		foreach ($options as $option => $value) {
			$dflt_options[$option] = $value;
		}

		curl_setopt_array($this->curl, $dflt_options);
		$response = curl_exec($this->curl);
		if(!$response || !$this->curl) {
			$error = curl_error($this->curl);
			trigger_error("$error\n", E_USER_WARNING);
			curl_close($this->curl);
			return false;
		}
		
		$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		$headers = substr($response, 0, $header_size);
		$headers = explode("\r\n", $headers);
		$response_headers = array();
		$response_headers['status'] = array_shift($headers);
		foreach($headers as $header) {
			if(!@empty($header)) {
				$h = explode(': ', $header);
				if(count($h) > 1) $response_headers[$h[0]] = $h[1];
			}
		}
		$contents = substr($response, $header_size);
		curl_close($this->curl);

		return array('headers' => $response_headers, 'contents' => $contents);
	}

}

?>