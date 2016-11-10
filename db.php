<?php

	$dbDetails = array(
		"servername" => "localhost",
		"username" => "dev",
		"password" => "sql_dev_pass",
		"dbname" => "calcTip",
		'tablename' => "bill_history"
	);
	
	// Connect up
	$link = connectLink($dbDetails);


	function connectLink($dbDetails) {

		try {
			$link = new PDO("mysql:host=" . $dbDetails['servername'] . ";dbname=" . $dbDetails['dbname'], $dbDetails['username'], $dbDetails['password']);
		} catch (PDOException $e) {

			echo 'PDO Connection failed: ' . $e->getMessage().'. ';
			closeLink();
			
			exit();
		}
		
		return $link;
	}


	// Close DB
	function closeLink() {
		return $link = null;
	}
	
?>