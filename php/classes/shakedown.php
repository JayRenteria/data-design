<?php
// first, require your class
require_once("comments.php");

// use constructor to create an object
$comments = new Comments(null, 1, "yay! object oriented!");

// connect to mySQL and populate the database
// yes this is bad but we'll isolate these parameters later...
try {
	// tell mysqli to throw exceptions
	mysqli_report(MYSQLI_REPORT_STRICT);

	// no go ahead and connect
	$mysqli = new mysqli("localhost", "jrenteria", "block(walkie4567sinus", "jrenteria");

	// now insert into mysql
	$comments->insert($mysqli);

	// finally, disconnect from mysql
	$mysqli->close();

	// var_dump the result to affirm we got a real primary key
	var_dump($comments);
} catch (Exception $exception) {
	// echo the error message and location for now
	echo "Exception: " . $exception->getMessage() . "<br/>";
	echo $exception->getFile() . ":" . $exception->getLine();
}

?>