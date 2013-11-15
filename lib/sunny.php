<?php 
include 'simple_html_dom.php';
include 'database.php';

class Sunny {

	/* target site */
	public $site;

	/* storage for harvested links */
	public $crawl = array();

	/* database link */
	public $db;
	
	public function __construct($site, $db) {
		$this->site = $site;
		$this->db = $db;
	}

	public function init() {
		echo "Setting sail..\r\n";
		$this->harvest($this->site, $this->site);
		$links = count($this->crawl) - 1;

		for($i=0; $i <= count($this->crawl); $i++) {
			echo "[{$i}/{$links}] Crawling {$this->crawl[$i]}\r\n";
			$this->harvest($this->crawl[$i], $this->site);
		}
	}

	public function resume($site_id) {
		echo "Resuming sunny..\r\n";
		$result = $this->db->query("SELECT link FROM site_index WHERE site_id={$site_id} AND indexed=0");

		echo "Loading links from database..\r\n";
		while ($row = $result->fetch_assoc()) {
			$this->crawl[] = $row['link'];
		}
		echo "Loaded {$result->num_rows} links. Resuming in 3s\r\n";
		sleep(3);

		$links = count($this->crawl) - 1;
		for($i=0; $i <= count($this->crawl); $i++) {
			echo "[{$i}/{$links}] Crawling {$this->crawl[$i]}\r\n";
			$this->harvest($this->crawl[$i], $this->site);
		}
	}

	public function http_request($url, $options = array()) {
		$ch = curl_init();
		$dflt_options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER =>1,
		);

		foreach ($options as $option => $value) {
			$dflt_options[$option] = $value;
		}

		curl_setopt_array($ch, $dflt_options);
		$response = curl_exec($ch);
		if(!$response || !$ch) {
			echo curl_error($ch);
			echo $url;
			curl_close($ch);
			return false;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
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
		curl_close($ch);

		return array('headers' => $response_headers, 'contents' => $contents);
	}

	public function harvest($url, $referrer) {

		$options = array(
			CURLOPT_REFERER => $referrer,
			CURLOPT_CONNECTTIMEOUT => 10
		);
		$response = $this->http_request($url, $options);
		$body = str_get_html($response['contents']);

		if(in_array($url, $this->crawl)) {
			$this->db->query("UPDATE site_index SET indexed = '1' WHERE link='{$url}';");
		}

		if(! is_object($body)) {
			echo "No response content: {$url}\r\n";
			return false;
		}

		$links = $body->find('a');

		/* add link to queue */
		foreach($links as $link) {
			if(! in_array($link->href, $this->crawl) && preg_match("#^{$this->site}#", $link->href, $m))
			{
				$result = $this->db->query("SELECT link FROM site_index WHERE link='{$link->href}'");
				if($result->num_rows <= 0){
					$result = $this->db->query("INSERT INTO site_index VALUES(null, '1', '{$link->href}', '0')");
				} else {
					echo "Has indexed: {$link->href}\r\n";
				}
				$this->crawl[] = $link->href;
			}
		}

		return $body;
	}
}

?>