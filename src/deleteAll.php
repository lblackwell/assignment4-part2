<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Connect to database
// NOTE: PASSWORD must be replaced with ONID DB password
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "blackwlu-db", PASSWORD, "blackwlu-db");

// Check connection
if(!$mysqli || $mysqli->connect_errno)
{
    echo "Connection error ".$mysqli->connect_errno." ".$mysqli->connect_error;
}

// Prepare prepared statement
if(!($stmt = $mysqli->prepare("DELETE FROM video")))
{
    echo "Prepare failed: (".$mysqli->errno.")".$mysqli->error;
}

// Execute prepared statement
if(!$stmt->execute())
{
    echo "Execute failed: (".$stmt->errno.")".$stmt->error;
}

mysqli_close($mysqli);

header("location: videoTable.php");
exit();
?>