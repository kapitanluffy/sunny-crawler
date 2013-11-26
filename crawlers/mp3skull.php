<?php

class Mp3skull extends Sunny {

	public function __construct($site, $db) {
		parent::__construct($site, $db);
	}

	public function get_download_link($link, $referer) {
		$options = array(
			CURLOPT_REFERER =>$referer,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_NOBODY => true,
		);
		$link = str_replace(' ', '%20', $link->href);
		$response = $this->curl->get($link, $options, 10);
		if(!preg_match('#(200|301|302|303|307|308)#', $response['headers']['status'])) {
			$log = "Not OK [{$response['headers']['status']}]: {$link}\r\n";
			$this->logger->write($log);
			return false;
		}
		if(isset($response['headers']['Location'])) {
			$link = $response['headers']['Location'];
		}

		return $link;
	}

	public function parse_meta($meta) {
		$return = array('title' => '', 'artist' => '');
		$meta = $meta->innertext;
		$meta = str_replace('mp3', '', $meta);
		$meta = explode(' - ', $meta);
		if(count($meta) != 2) return false;
		$return['artist'] = trim($meta[0]);
		$return['title'] = trim($meta[1]);
		return $return;
	}

	public function on_body($url, $body) {
		$targets = $body->find('#song_html #right_song');
		$data = array();
		$ignore_url_pattern = implode('|', $this->ignore_url_pattern);

		if(@empty($targets)) return false;

		foreach($targets as $i => $block) {
			$meta = $block->find('b');
			$link = $block->find('a[target="_blank"]');

			if(preg_match('#('. $ignore_url_pattern .')#', $link[0]->href)) {
				$log = "Ignored: {$link[0]->href}\r\n";
				$this->logger->write($log);
				continue;
			}

			$link = $this->get_download_link($link[0], $url);
			$meta = $this->parse_meta($meta[0]);

			if( $meta['artist'] == '' || $meta['title'] == '' || !preg_match('#\.mp3$#', $link)) continue;

			$link = $this->db->real_escape_string($link);
			$artist = $this->db->real_escape_string($meta['artist']);
			$title = $this->db->real_escape_string($meta['title']);
			$url = $this->db->real_escape_string($url);
			
			$this->db->query("INSERT INTO storage_{$this->table} VALUES(-1, '{$artist}', '{$title}', '{$link}', '{$url}', SHA1('{$link}')) ON DUPLICATE KEY UPDATE hash=hash");
			if ($this->db->error) $this->quit($this->db->error);
			$this->total_found += 1;
			
			$hash = hash('sha1', $link);
			$artist = $this->add_keyword($meta['artist'], $hash, 'artist');
			$title = $this->add_keyword($meta['title'], $hash, 'title');

			$log = "Found! {$title} by {$artist} - {$link}\r\n";
			$this->logger->write($log);
		}
	}
}

?>