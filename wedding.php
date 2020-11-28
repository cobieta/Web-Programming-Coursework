<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Wedding (Task 5)</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type = "text/javascript">
// Global search values:
var p;
var startString;
var endString;
var startDate;
var endDate;
var minDate = new Date("2020-01-01");
var maxDate = new Date("2020-12-31");
var grades = [];

	// Function to get all the venues from the database and display them when the page loads.
	$(document).ready(function(){
		$(".details").hide();
		var sql = "SELECT name FROM venue";
		$.get("query_database.php",{'sql':sql}, function(data,status){
			var listDisplay = "";
			for(var i=0; i<data.length; i++){
				listDisplay += "<li>"+data[i].name+"</li>";
			}
			$("#results").html(listDisplay);
			console.log(status);
		},"json");	
	});	

	// Function to validate the data entered by the user in the form when the user presses the search button 
	// before using it to query the database.
	function validateForm() {
		$("#resultsHead").text("Venues matching your search criteria:");
		p = $("#partySize").val();
		startString = $("#startDate").val();
		endString = $("#endDate").val();
		startDate = new Date(startString);
		endDate = new Date(endString);
		var daysBetween = (endDate.getTime() - startDate.getTime()) / (1000 * 3600 * 24);
		grades = [];
		$.each($("input[name='catGrade']:checked"), function(){            
            grades.push($(this).val());
        });
		
		if (isNaN(p) || p < 1 || p > 1000) {
			alert('Error, party size should be a number between 1 and 1000.');
		} else if (startDate < minDate || startDate > maxDate) {
			alert('Error, the start date should be between 01/01/2020 and 31/12/2020.');
		} else if (endDate < minDate || endDate > maxDate) {
			alert('Error, the end date should be between 01/01/2020 and 31/12/2020.');
		} else if (daysBetween >= 7) {
			alert('Error, the end date should be at most a week from the start date.');
		} else if (grades.length < 1) {
			alert('Error, at least one catering grade should be selected.');
		} else {
			// Passed all validation checks, so create the SQL statement.
			var sql = "SELECT venue_id, name FROM venue WHERE capacity >= "+p+" ";
			sql += "AND venue_id <> ALL (SELECT venue_id FROM venue_booking ";
			sql += "WHERE (date_booked >= '"+startString+"') AND (date_booked <= '"+endString+"') ";
			sql += "GROUP BY venue_id HAVING COUNT(date_booked) >= "+(daysBetween+1)+") ";
			sql += "AND venue_id = ANY (SELECT DISTINCT venue_id FROM catering WHERE grade = "+grades[0]+" ";
			if (grades.length > 1) {
				for (var i=1; i<grades.length; i++) {
					sql += "OR grade = "+grades[i]+" ";
				}
			}
			sql += ")";
			
			// send the query to the database and display the results.
			$.get("query_database.php",{'sql':sql}, function(data,status){
				var listDisplay = "";
				$("#results").empty();
				if (data.length > 0) {
					for(var i=0; i<data.length; i++){
						listDisplay += "<li value="+data[i].venue_id+" class='clickable' onClick='getDetails(this);'>"+data[i].name+"</li>";
					}
					$("#results").html(listDisplay);
				} else {
					$("#resultsHead").text("No venues found matching your search criteria:");
				}
				console.log(status);
			},"json");
			// Panel effects
			$(".details").show();
			$("#detailsTable").hide();
		}
	}
	
	// Function to fetch and display the details on a selected venue from the displayed list.
	function getDetails(caller) {
		$("#detailsTable").fadeIn(1800);
		var id = caller.value;
		var sql = "SELECT * FROM venue WHERE venue.venue_id = "+id;
		$.get("query_database.php",{'sql':sql}, function(data,status){
			$("#detailsName").text(data[0].name);
			$("#detailsCapacity").text(data[0].capacity);
			if (data[0].licensed == 1) {
				$("#detailsLicensed").text("Yes");
			} else {
				$("#detailsLicensed").text("No");
			}
			$("#detailsWeekdayPrice").text(data[0].weekday_price);
			$("#detailsWeekendPrice").text(data[0].weekend_price);
			
			// Get the dates the venue has already been booked for:
			sql = "SELECT * FROM venue_booking WHERE venue_id = "+id;
			sql += " AND date_booked >= '"+startString+"' AND date_booked <= '"+endString+"'";
			$.get("query_database.php",{'sql':sql}, function(bookedDates,status){
				// Create the list of dates between the start and end dates. 
				var datesWithinRange = [];
				var nextDate = new Date(startDate);
				while (nextDate <= endDate) {
					var formattedDate = nextDate.toISOString();
					formattedDate = formattedDate.slice(0, 10);
					var found = false;
					// Check if this venue has already been booked on dates within the user's selected range.
					if (bookedDates.length > 0) {
						for (var i=0; i<bookedDates.length; i++) {
							if (bookedDates[i].date_booked == formattedDate) {
								found = true;
								break;
							}
						}
					}
					if (!found) {
						datesWithinRange.push(formattedDate);
					}
					nextDate.setDate(nextDate.getDate() + 1);
				}
				
				// Display the available dates in the details table.
				var dates = "";
				dates += datesWithinRange[0];
				if (datesWithinRange.length > 1) {
					for (var i=1; i<datesWithinRange.length; i++) {
						dates += (", " + (datesWithinRange[i]));
					}
				}
				$("#detailsDatesAvailable").text(dates);
			},"json");
			
			// Get the catering grades and costs.
			sql = "SELECT grade, cost FROM catering WHERE venue_id = "+id+" AND (grade = "+grades[0];
			if (grades.length > 1) {
				for (var i=1; i<grades.length; i++) {
					sql += " OR grade = "+grades[i]+" ";
				}
			}
			sql += ")";
			$.get("query_database.php",{'sql':sql}, function(cateringGrades,status){
				var optionsDisplay = "";
				$("#grade").empty();
				for(var i=0; i<cateringGrades.length; i++) {
					optionsDisplay += " <option value="+cateringGrades[i].cost+">"+cateringGrades[i].grade+"</option> ";
				}
				$("#grade").html(optionsDisplay);
				$("#detailsCatCost").text(cateringGrades[0].cost);
				
				// Calculate the total cost of renting the venue for a weekend or weekday with the cost of catering for 
				// everyone (the party size entered) included. 
				var totalWeekdayCost = parseInt(data[0].weekday_price) + (cateringGrades[0].cost * p);
				var totalWeekendCost = parseInt(data[0].weekend_price) + (cateringGrades[0].cost * p);
				$("#detailsTotalWeekdayCost").text(totalWeekdayCost);
				$("#detailsTotalWeekendCost").text(totalWeekendCost);
			},"json");
		},"json");
	}
	
	// Function to change the catering grade cost shown and recalculate the total cost according to the grade selected.
	function showCatCost(thisGrade) {
		$("#detailsCatCost").text(thisGrade.value);
		var totalWeekdayCost = parseInt($("#detailsWeekdayPrice").text()) + (thisGrade.value * p);
		var totalWeekendCost = parseInt($("#detailsWeekendPrice").text()) + (thisGrade.value * p);
		$("#detailsTotalWeekdayCost").text(totalWeekdayCost);
		$("#detailsTotalWeekendCost").text(totalWeekendCost);
	}
</script>
<style type="text/css">
body {
	background-color: #D6D6D6;
}
.center {
	text-align:center;
}
body,td,th {
	color: #474747; 
	font-family:Book Antiqua;
}
.larger {
	font-size:125%;
	text-align:left;
}
table {
	margin-left:auto;
	margin-right:auto;
	width:60%;
	border-collapse: collapse;
}
th, td {
  padding: 5px;
}
img {
	width: 320px;
	height: 213px;
}
h1 {
	font-family:Book Antiqua;
	font-size:400%;
}
div.options {
	border-top-style: double;
	border-bottom-style: double;
}
div.details {
	border-top-style: double;
}
li.clickable:hover {
	cursor: pointer; 
	background-color: white;
}
</style>
</head>
<body>

<!-- Page heading -->
<h1 class="center">
<img src="wedding_photo_1.jpg" alt="wedding flower bouquet" style="float:left;">
<!-- Image credit: Trung Nguyen from Pexels (https://www.pexels.com/photo/bridge-and-groom-standing-while-holding-flower-bouquet-2959192/) under Creative Commons CC0 -->
<img src="wedding_photo_2.jpg" alt="wedding venue tables" style="float:right;">
<!-- Image credit: Craig Adderley from Pexels (https://www.pexels.com/photo/tables-with-flower-decors-2306281/) under Creative Commons CC0 -->
<br/>Find your <em>perfect</em><br/> wedding venue
</h1>

<!-- Options section -->
<div class="options">
<br/>
<!-- Form allowing the user to enter dates, party size and catering grade. -->
<form name="myForm" class="center">
<table border="1">
    <tr class="larger">
		<th><label for="partySize">Party Size:</label></th>
		<td colspan="5"><input name="partySize" type="number" class="larger" id="partySize" value="100" min="1" max="1000"></td>
	</tr>
	<tr class="larger">
		<th>Catering Grades:</th>
		<td class="center"><label for="cat1">1</label>
			<input name="catGrade" type="checkbox" class="larger" id="cat1" value="1"></td>
		<td class="center"><label for="cat2">2</label>
			<input name="catGrade" type="checkbox" class="larger" id="cat2" value="2"></td>
		<td class="center"><label for="cat3">3</label>
			<input name="catGrade" type="checkbox" class="larger" id="cat3" value="3"></td>
		<td class="center"><label for="cat4">4</label>
			<input name="catGrade" type="checkbox" class="larger" id="cat4" value="4"></td>
		<td class="center"><label for="cat5">5</label>
			<input name="catGrade" type="checkbox" class="larger" id="cat5" value="5"></td>
	</tr>
	<tr class="larger">
		<th>Date Range:</th>
		<td colspan="2"><input name="startDate" type="date" class="larger" id="startDate" value="2020-01-01" min="2020-01-01" max="2020-12-31"></td>
		<th>To:</th>
		<td colspan="2"><input name="endDate" type="date" class="larger" id="endDate" value="2020-12-31" min="2020-01-01" max="2020-12-31"></td>
	</tr>
</table>
</br>
<button id="searchButton" type="button" onClick="validateForm();" class="larger">Search</button>
</form>
</div>

<!-- Results section -->
<h2 id="resultsHead" class="center">All available venues:</h2>
<div class="center">
<ul id="results" class="center" style="font-size:125%;">
<!-- jQuery fills the list depending on whether the page has just loaded or the user selected some search criteria. -->
</ul>
</br>
</div>

<!-- More details section -->
<div class="details">
<!-- This section is completedly hidden until the user presses the search button. -->
<h2 id="detailsHead" class="center">Select a venue above to view more details:</h2>
<!-- The table is hidden by JQquery until the user selects a venue to view more details about and the venue details are filled out using JQquery -->
<table id="detailsTable" class="center" border="1">
	<tr class="larger">
		<th>Name:</th>
		<td id="detailsName" colspan="2"></td>
	</tr>
	<tr class="larger">
		<th>Capacity:</th>
		<td id="detailsCapacity" colspan="2"></td>
	</tr>
	<tr class="larger">
		<th>Licensed:</th>
		<td id="detailsLicensed" colspan="2"></td>
	</tr>
	<tr class="larger">
		<th>Dates available:</th>
		<td id="detailsDatesAvailable" colspan="2"></td>
	</tr>
	<tr class="larger">
		<th><form name="cateringCost">
			<label for="grade">Cost of catering per person (£) at grade :</label>
			<select id="grade" name="grade" onchange="showCatCost(this);"> 
				<!-- JQquery is used to display the catering grade options available for the specific venue and selected catering grades -->
			</select>
		</form></th>
		<td id="detailsCatCost" colspan="2"></td>
	</tr>
	<tr class="larger">
		<th rowspan="2">Price per day (£):</th>
		<th>Weekdays</th><th>Weekends</th>
	</tr>
	<tr class="larger">
		<td id="detailsWeekdayPrice"></td>
		<td id="detailsWeekendPrice"></td>
	</tr>
	<tr class="larger">
		<th>Total cost per day (£) including catering for everyone:</th>
		<td id="detailsTotalWeekdayCost"></td>
		<td id="detailsTotalWeekendCost"></td>
	</tr>
</table>
</div>
</br>

</body>
</html>