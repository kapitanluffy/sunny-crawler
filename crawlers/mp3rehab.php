<?php

class Mp3rehab extends Sunny {

	public function __construct($site, $db) {
		parent::__construct($site, $db);
	}

	public function get_download_link($link, $referer) {
		$options = array(
			CURLOPT_REFERER =>$referer,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_NOBODY => true,
		);
		$url = str_replace(' ', '%20', $link->href);
		$response = $this->curl->get($url, $options);
		if(! $response) return false;
		$url = $response['headers']['Location'];

		return $url;
	}

	public function parse_meta($meta) {
		$return = array('title' => '', 'artist' => '');
		$return['title'] = $meta[0]->innertext;
		$return['artist'] = $meta[1]->innertext;

		return $return;
	}

	public function prioritize($url, $body) {
		if(preg_match_all('#\/play\/#', $url)) {
			return 9;
		}
		return 0;
	}

	public function on_body($url, $body) {

		$links = $body->find('.dllink a');
		if(! $links) $links = $body->find('.filedownload a');

		$metas = $body->find('.infotext a');
		if(! $metas) $metas = $body->find('.fileinfo a');

		if(! $links || ! $metas) {
			return false;
		}

		$link = $this->get_download_link($links[0], $url);
		$meta = $this->parse_meta($metas);

		if(! $link || $meta['artist'] == '' || $meta['title'] == '') {
			return false;
		}

		$hash = hash('sha1', $link);
		
		$artist = $this->db->real_escape_string($meta['artist']);
		$title = $this->db->real_escape_string($meta['title']);
		$link = $this->db->real_escape_string($link);
		$url = $this->db->real_escape_string($url);

		$this->db->query("INSERT INTO storage_{$this->table} VALUES(-1, '{$artist}', '{$title}', '{$link}', '{$url}', '{$hash}') ON DUPLICATE KEY UPDATE hash=hash");
		
		if ($this->db->error) $this->quit(__LINE__ . ' ' .$this->db->error);

		$artist = $this->add_keyword($meta['artist'], $hash, 'artist');
		$title = $this->add_keyword($meta['title'], $hash, 'title');
		
		$log = "Found! {$title} by {$artist} - {$link}\r\n";
		$this->logger->write($log);
		$this->total_found += 1;

	}
}

?>