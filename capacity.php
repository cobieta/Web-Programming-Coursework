<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Capacity Task</title>
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

// Get the minimum and maximum capacity from the form and store and validate it.
$minCapacity = $_GET['minCapacity'];
$maxCapacity = $_GET['maxCapacity'];

if ((is_numeric($minCapacity) && ((is_numeric($maxCapacity)) && ($minCapacity <= $maxCapacity)))) {
	$sql="SELECT name, weekend_price, weekday_price FROM `venue` WHERE licensed = '1' AND capacity >= $minCapacity AND capacity <= $maxCapacity";
	$res =& $db->query($sql);
	
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	// Produce the table result:
	echo '<table border="1">';
	echo '<tr class="larger"><th>Name</th><th>Weekend Price</th><th>Weekday Price</th></tr>';
	
	while ($row = $res->fetchRow()) {
		echo '<tr class="larger">';
		echo '<td>'.$row[strtolower('name')].'</td>';
		echo '<td>'.$row[strtolower('weekend_price')].'</td>';
		echo '<td>'.$row[strtolower('weekday_price')].'</td>';
		echo '</tr>';
	}
	
	echo '</table>';
	
} else {
	echo "<p>Error, Check that the minimum and maximum capacity are numbers and that the minimum capacity is less than the maximum capacity.</p>";
}


?>

</body>
</html>