<?php
	$conn = new mysqli('localhost', 'root', '', 'apsystem',4306);

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>