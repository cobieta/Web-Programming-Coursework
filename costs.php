<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Costs Task</title>
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

// Get the date and party size from the form and store and validate them.
$date = $_GET['date'];
$partySize = $_GET['partySize'];

// Reformat the date.
$has2Slash = substr_count($date, '/');
if ($has2Slash === 2) {
	list($d,$m,$y) = explode('/',$date);
	$formattedDate = $y.'-'.$m.'-'.$d;
} 

$validateDate = false;
$isAWeekend = false;

// Validate the dates and find out whether the day entered is a weekend or weekday.
if (($has2Slash !== 2) || (strtotime($formattedDate) === false)) {
	echo "<p>Error, Check that a date has been entered in the correct format.</p>";
} else {
	$validateDate = true;
	$convertedDate = strtotime($formattedDate);
	$dayOfWeek = date('w',$convertedDate);
	if (($dayOfWeek == 6) || ($dayOfWeek == 0)) {
		$isAWeekend = true;
	}	
}

if ((is_numeric($partySize)) && ($validateDate == true)) {
	// Building the SQL statement and querying the database.
	$sql='SELECT name,';
	if ($isAWeekend == true) {
		$sql .= ' weekend_price ';
	} else {
		$sql .= ' weekday_price ';
	}
	
	$sql .= "FROM venue WHERE capacity >= $partySize AND venue_id <> ALL (SELECT venue_id FROM venue_booking WHERE date_booked = '$formattedDate')";
	$res =& $db->query($sql);
	
	if(PEAR::isError($res)){
		die($res->getMessage());
	}
	
	// Produce the table result:
	echo '<table border="1">';
	echo '<tr class="larger"><th>Name</th>';
	if ($isAWeekend == true) {
		echo '<th>Weekend Price</th></tr>';
	} else {
		echo '<th>Weekday Price</th></tr>';
	}
	
	while ($row = $res->fetchRow()) {
		echo '<tr class="larger">';
		echo '<td>'.$row[strtolower('name')].'</td>';
		if ($isAWeekend == true) {
			echo '<td>'.$row[strtolower('weekend_price')].'</td>';
		} else {
			echo '<td>'.$row[strtolower('weekday_price')].'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	
} else  if (!(is_numeric($partySize))) {
	echo "<p>Error, Check that the party size is a number.</p>";
}

?>

</body>
</html>