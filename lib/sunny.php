<?php 
include 'database.php';
include 'simple_html_dom.php';
include 'curl_lib.php';

class Sunny {

	/* target site */
	public $site;

	/* table */
	public $table;

	/* storage for harvested links */
	public $crawl = array();
	/* current pointer for the crawl */
	public $crawl_index = 0;

	/* database link */
	public $db;

	/* curl object */
	public $curl;

	private $append_queue = false;
	protected $queued = 0;
	protected $ignore_url_pattern = array('javascript\:','mailto\:', '\#');
	public $current_links = 0;
	public $total_links = 0;

	/* for elapsed time */
	public $script_start;
	public $script_end;
	
	/* execute once body is harvested */
	public function on_body($url, $body) {}
	/* execute once links are harvested */
	public function on_links($url, $links) {}
	public function prioritize($url, $body) {
		/* determine link priority */
	}

	public function __construct($site, $db) {
		declare(ticks = 1);
		$this->script_start = microtime(true);
		$this->site = $site;
		$this->db = $db;
		$this->curl = new curl_lib();

		pcntl_signal(SIGTERM, array($this, 'quit'));
		pcntl_signal(SIGINT, array($this, 'quit'));
		set_error_handler(array($this, 'on_error'));
	}

	public function init($table, $threads = 10, $limit = 100) {
		$result = $this->db->query("SHOW TABLES LIKE '%index_{$table}%';");

		if($result->num_rows <= 0) {
			$create_index  = " create table index_{$table} (indexed int(1) default 0, link varchar(767) unique, lastcrawl timestamp default CURRENT_TIMESTAMP, hash varchar(500));";
			$insert_url = "insert into index_{$table} values (0, '{$this->site}', CURRENT_TIMESTAMP, sha1(link));";
			$create_storage = "create table storage_{$table} (flag int(2) default -1, artist varchar(300), title varchar(300), link varchar(300), referrer varchar(300), hash varchar(767) unique);";

			$this->db->query($create_index);
			$this->db->query($insert_url);
			$this->db->query($create_storage);
		}

		if($this->append_queue) $this->crawl($table, $threads, $limit);
	}

	public function append_queue($bool) {
		$this->append_queue = $bool;
	}

	public function ignore_url_pattern($patterns = array()) {
		foreach($patterns as $pattern) {
			$this->ignore_url_pattern[] = $pattern;
		}
	}

	public function sanitize_url($url) {
		$l = strip_tags($url);
		$l = html_entity_decode($l);
		$l = $this->db->real_escape_string($l);

		return $l;
	}

	public function quit($msg = '') {
		$this->db->close();

		$script_end = microtime(true);
		$time = floor($script_end - $this->script_start);
		$hh = 00; $mm = 00;
		$ss = $time % 60;
		if($ss  > 0) $mm = ($time - $ss) / 60;
		$rm = $mm % 60;
		if($rm  > 0) $hh = ($mm - $rm) / 60;
		echo "{$hh}:{$mm}:{$ss}\r\n";
		echo "{$this->crawl_index}\r\n";
		echo "$msg\r\n";
		exit;
	}

	private function on_error($errno , $errstr, $errfile, $errline) {
		echo "\r\n\r\n";
		echo "ERROR #$errno: $errstr on $errfile line $errline";
		echo "\r\n\r\n";
		$this->quit();
	}

	private function prompt($msg = "Press enter to start") {
		echo "$msg\r\n";
		$fh = fopen("php://stdin", "r"); 
		$a = fgets( $fh); 
		fclose($fh); 
	}

	public function mask($url) {
		$a_url = parse_url($url);
		$host = $a_url['host'];
		preg_match_all('/(.*?)\./s', $host, $m);
		
		$domain = $m[1][0];
		if(isset($m[1][1])) {
			$domain = $m[1][1];
		}
		$len = strlen($domain);
		$masked = str_pad('', $len, '*');
		return str_replace($host,"www.$masked.com", $url);
	}

	public function crawl_all($table, $threads = 10, $limit = 100) {
		$this->table = $table;
		$offset = 0;
		do {
			echo "\r\nCrawling all links! Press CTRL+C to exit\r\n";
			$result = $this->db->query("SELECT link FROM index_{$this->table} WHERE indexed=0 ORDER BY priority DESC LIMIT $offset,$limit");
			if($result->num_rows > 0) {
				$this->_crawl_algo($result, $threads);
			}
			$offset += $limit;
		} while($result && $result->num_rows > 0);
	}

	function _crawl_algo($result, $threads) {
		$this->crawl = array();
		$this->crawl_index = 0;
		$ignore_url_pattern = implode('|', $this->ignore_url_pattern);

		while ($row = $result->fetch_assoc()) {
			$this->crawl[] = $row['link'];
		}
		echo "Loaded {$result->num_rows} links.\r\n";

		$this->current_links = count($this->crawl);
		$this->total_links += $this->current_links;
		while($this->crawl_index < $this->current_links) {
			$curls = array();
			$i = $this->crawl_index;

			for($t = 0; $t < $threads && $i < $this->current_links; $t++){
				if(preg_match('#('. $ignore_url_pattern .')#', $this->crawl[$i])) {
					echo "Deleted: {$this->crawl[$i]}\r\n";
					$this->db->query("DELETE FROM index_{$this->table} WHERE link='{$this->crawl[$i]}' ");
					continue;
				}
				$curls[] = array(CURLOPT_URL => $this->crawl[$i++]);
			}
			$curl_count = count($curls);
			// echo "Starting {$curl_count} curl threads\r\n";
			$responses = $this->curl->multi_get($curls);

			$response_count = count($responses);
			if($response_count <= 0) $this->quit('received no response');
			// echo "Received $response_count responses\r\n";

			foreach($responses as $response) {
				if(! isset($this->crawl[$this->crawl_index])) break;
				echo "Crawling #{$this->crawl_index}: {$this->crawl[$this->crawl_index]}\r\n";
				$this->harvest($response, $this->site);
				$this->crawl_index++;
			}
		}
	}

	public function crawl($table, $threads = 10, $limit = 100, $offset = 0) {
		$this->table = $table;
		$result = $this->db->query("SELECT link FROM index_{$this->table} WHERE indexed=0  ORDER BY priority DESC LIMIT $offset,$limit");

		$this->_crawl_algo($result, $threads);

		$this->quit();
	}

	public function harvest($body) {
		$url = $this->crawl[$this->crawl_index];
		$ignore_url_pattern = implode('|', $this->ignore_url_pattern);

		if(empty($body)) $this->quit("No content body {$url}");

		$body = str_get_html($body);
		$this->on_body($url, $body);

		$links = $body->find('a');
		$this->on_links($url, $body);

		if(in_array($url, $this->crawl)) {
			unset($this->crawl[$this->crawl_index]);
			$this->db->query("UPDATE  index_{$this->table} SET indexed = '1', lastcrawl=CURRENT_TIMESTAMP WHERE link='{$url}';");
		}

		$found = 0; $queued = 0; $skipped = 0; $iv = '';

		foreach($links as $link) {
			if(preg_match('#('. $ignore_url_pattern .')#', $link, $m)) {
				continue;
			}

			if(! preg_match("#^http\:\/\/#", $link->href)) {
				$link_href = ltrim($link->href, '/');
				$link->href = $this->site . $link_href;
			}

			if(in_array($link->href, $this->crawl) || !preg_match("#^{$this->site}#", $link->href)) {
				continue;
			}

			$l = $this->sanitize_url($link->href);

			$result = $this->db->query("SELECT link FROM  index_{$this->table} WHERE link='{$l}' LIMIT 0,1");
			if($result->num_rows <= 0){
				$priority = $this->prioritize($link, $body);
				echo "Queued [P{$priority}]: {$l} \r\n";
				if($this->append_queue) $this->crawl[] = $l;
				$iv .= "(0, '{$l}', null, SHA1('$l'),0),";
				$queued++;
			}
		}
		$iv = trim($iv, ',');
		if($iv) $this->db->query("INSERT INTO index_{$this->table} VALUES $iv ON DUPLICATE KEY UPDATE lastcrawl=CURRENT_TIMESTAMP");
		
		if($this->db->error) {
			echo "INSERT INTO index_{$this->table} VALUES $iv ON DUPLICATE KEY UPDATE lastcrawl=CURRENT_TIMESTAMP\r\n";
			echo "{$this->db->error}\r\n";
			$this->quit();
		}

		if($links) {
			$found = count($links);
			$skipped = $found - $queued;
			$this->queued += $queued;
		}

		$remaining = count($this->crawl);
		$finished = $this->crawl_index + 1;
		$percent = ($finished / $this->current_links) * 100;
		$percent = number_format($percent, 2);
		echo "Progress {$percent}% [ All {$this->total_links} / Finished {$finished} / Total {$this->current_links} / Remaining {$remaining} / Found {$found} / Skipped {$skipped} / Queued {$this->queued} ]\r\n";
		return true;
	}
}

?>