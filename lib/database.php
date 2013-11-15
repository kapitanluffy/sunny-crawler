<?php 

$db = new mysqli('localhost','root','','sunny');
if ($db->connect_errno) {
	echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $this->db->connect_error;
	die;
}

?>