<?php

/* Crawler for mp3rehab.com */

class Mp3rehab extends Sunny {

	public $link;
	public $meta;
	public $body;

	public function __construct($site) {
		parent::__construct($site);
	}

	public function set_download_link($link) {
		$this->link = $link;
	}

	public function get_download_link($referer = '') {

		$links = $this->body->find($this->link);
		if(! $links) { return false; }

		$link = $links[0];
		$options = array(
			CURLOPT_REFERER =>$referer,
			CURLOPT_FOLLOWLOCATION => false,
		);
		$response = $this->http_request($link->href, $options);

		$link = @$response['headers']['Location'];

		/* check if valid link */
		if(preg_match("#^http\:\/\/#", $link, $m)) {
			return str_replace(' ', '%20', $link);
		}
	}

	public function set_meta_info($meta) {
		$this->meta = $meta;
	}

	public function get_meta_info() {

		$meta = $this->body->find($this->meta);
		if(!$meta) { return false; }
			
		return array('title' => $meta[0]->innertext, 'artist' => $meta[1]->innertext);
	}

	public function harvest($url, $referrer) {

		/* ignore the download script */
		if(preg_match("#dl\.php#", $url, $m)) {
			return false;
		}

		$this->body = parent::harvest($url, $referrer);
		if(! is_object($this->body)) {
			return false;
		}
		
		$links = $this->get_meta_info();
		$links['link'] = $this->get_download_link($url);

		if($links['link']) {
			echo "Found! {$links['title']} by {$links['artist']} - {$links['link']}\r\n";
			$this->db->query("INSERT INTO mp3rehab VALUES(null, '{$links['artist']}', '{$links['title']}', '{$links['link']}', '{$url}');");
		}
	}
}