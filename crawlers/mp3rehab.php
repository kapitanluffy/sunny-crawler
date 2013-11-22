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

		if(! $link || $meta['artist'] == '') {
			return false;
		}

		$link = $this->sanitize_url($link);
		$artist = $this->sanitize_url($meta['artist']);
		$title = $this->sanitize_url($meta['title']);
		$url = $this->sanitize_url($url);

		$this->db->query("INSERT INTO storage_{$this->table} VALUES(-1, '{$artist}', '{$title}', '{$link}', '{$url}', SHA1('{$link}')) ON DUPLICATE KEY UPDATE hash=hash");
		if ($this->db->error) $this->quit($this->db->error);

		echo "Found! {$title} by {$artist} - {$link}\r\n";
	}
}

?>