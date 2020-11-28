<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Details Task</title>
<style type="text/css">
body {
	font-family: "Apple Chancery", Times, serif;
	background-color: #D6D6D6;
}
.center {
	text-align:center;
}
body,td,th {
	color: #06F; 
}
.larger {
	font-size:larger;
	text-align:left;
}
table {
	margin-left:auto;
	margin-right:auto;
	width:30%;
}
th, td {
  padding: 5px;
}
</style>
</head>
<body>

<?php

require_once 'MDB2.php';

// Connect to the database.
include "wedding-mysql-connect.php";  
 
$host='localhost';
$dbName='coa123wdb';

$dsn = "mysql://$username:$password@$host/$dbName"; 
$db =& MDB2::connect($dsn); 

if(PEAR::isError($db)){ 
    die($db->getMessage());
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);

// Get the venue ID from the form and store and validate it.
$venueId = $_GET['venueId'];

if ((is_numeric($venueId) && (($venueId > 0) && ($venueId <= 10)))) {
	$sql="SELECT * FROM `venue` WHERE venue_id = $venueId";
	$res =& $db->query($sql);
	
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	// Produce the table result:
	echo '<table border="1">';
	$row = $res->fetchRow();
	
	echo '<tr class="larger"><th>Venue ID</th><th>'.$venueId.'</th></tr>';
	echo '<tr class="larger"><td>Name</td><td>'.$row[strtolower('name')].'</td></tr>';
	echo '<tr class="larger"><td>Capacity</td><td>'.$row[strtolower('capacity')].'</td></tr>';
	echo '<tr class="larger"><td>Weekend Price (£)</td><td>'.$row[strtolower('weekend_price')].'</td></tr>';
	echo '<tr class="larger"><td>Weekday Price (£)</td><td>'.$row[strtolower('weekday_price')].'</td></tr>';
	
	// Display yes/no depending on whether the venue is licensed or not.
	if ($row[strtolower('licensed')] == 0) {
		echo '<tr class="larger"><td>Licensed</td><td>No</td></tr>';
	} else {
		echo '<tr class="larger"><td>Licensed</td><td>Yes</td></tr>';
	}
	
	echo '</table>';
} else {
	echo "<p>Error, Check that the Venue ID is a number and that it is between 1 and 10.</p>";
}


?>

</body>
</html>