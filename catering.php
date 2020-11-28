<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Catering Task</title>
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

$allValidated = false;

// Get the values from the form and store them. 
$min = $_GET['min'];
$max = $_GET['max'];

$cost = array($_GET['c1'], $_GET['c2'], $_GET['c3'], $_GET['c4'], $_GET['c5']);

// Validate the form values.
if ((is_numeric($min)) && (is_numeric($max)) && ($min <= $max)) {
	$NumRows = ($max - $min) / 5;
	$rowLabel = $min;
	foreach ($cost as $c) {
		if ((is_numeric($c)) && ($c > 0)) {
			$allValidated = true;
		} else {
			$allValidated = false;
			echo "<p>Error, Check that all cost grades are numbers greater than 0.</p>";
			break;
		}
	}
} else {
	echo "<p>Error, Check that min and max party size are numbers and that min is less than max.</p>";
}

// Produce the table result:
if ($allValidated == true) {
	echo '<table border="1">';
	echo '<tr class="larger">';
	echo '<th>Cost per person → <br/>Party size ↓</th>';
	foreach ($cost as $c) {
		echo '<th>'.$c.'</th>';
	}
	echo '</tr>';
	for ($i=0; $i<=$NumRows; $i++) {
		echo '<tr class="larger">';
		echo '<th>'.$rowLabel.'</th>';
		foreach ($cost as $c) {
			echo '<td>'.$c*$rowLabel.'</td>';
		}
		echo '</tr>';
		$rowLabel += 5;
	}
	echo '</table>';
}

?>

</body>
</html>