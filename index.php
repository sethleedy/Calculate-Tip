<?php

// Setup Vars
$myFile = htmlspecialchars($_SERVER['PHP_SELF']);

// Spit out the HTML Headers
echo_header($myFile);

//var_dump($_SERVER);
//var_dump($_REQUEST);

// Check summitted data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST["btnCalculate"])) {
	$dblBillAmount = floatval($_REQUEST["amountBill"]);	// $100
	$dblBillTip = floatval($_REQUEST["amountTip"]);		// 10%
	$frmErrors=false;
	
	// If valid input, continue on. Else, redisplay the calc form and an error message.
	if (!is_double($dblBillAmount) || !is_double($dblBillTip)) {
		$frmErrors=true;
	}
	
	// Make sure numbers were added
	if ( !is_numeric($dblBillAmount) || !is_numeric($dblBillAmount) ) {
		$frmErrors=true;
	}
	
	// Make sure we have numbers and the bill amount is above $0
	if ( $dblBillAmount <= 0 ) {
		$frmErrors=true;
	}
	
	
	if ($frmErrors == true) {
		display_Calculator($myFile);
		display_Bad_Msg("Something is wrong with the submission, try again.");
	} else {
		
		// Calc the math
		$dblBillTipPercent=($dblBillAmount * $dblBillTip) / 100;	// 10% of $100 = $10
		$dblBillTotalWithTip=$dblBillTipPercent + $dblBillAmount;	// Total: $110
		
		// Display in message
		// Link to History
		display_Good_Msg("Your bill is $".$dblBillAmount.".<br> Tip amount of ".$dblBillTip."% is $".$dblBillTipPercent.".<br> Total Bill, with tip included, equals: $".$dblBillTotalWithTip.".");
		
		// Link to History
		display_Info_Msg("<a href='".$myFile."?viewHistory'>View calculation history</a>");
		
		// Insert into DB
		$sqlInsert = array($dblBillAmount, $dblBillTipPercent, $dblBillTotalWithTip);
		insertDB($sqlInsert);
	
		// Display calc for another run
		display_Calculator($myFile);
	}
	
} else {
	
	// If we were passed the ?viewHistory
	if (isset($_REQUEST["viewHistory"])) {
		display_history();
		display_Calculator($myFile);
	} else {
		display_Calculator($myFile);	
	}
	
}


// echo the HTML for the </body> and </html>
echo_footer($myFile);

//// End Code Run

//// Functions

// Insert History into DataBase
function insertDB($arrDBData) {
	// Include the DB connection details
	include_once("db.php");
	
	try {
		$statement = $link->prepare("INSERT INTO " . $dbDetails["tablename"] . "(billamount, tipamount, totalamount) VALUES(?, ?, ?)");
    	$statement->execute($arrDBData); // execute for INSERTs. Use passed Array for the data to INSERT.
		
		// Close Connection
		$statement=null;
		closeLink();
		
    } catch (PDOException $e) {
    
        if ($i == 1) {
            // First time ? retry
            connectLink();
        } else {
            // Second time, KO
            $statement = "(unknown)";
            echo 'PDO Connection failed: ' . $e->getMessage().'. ';
        }
    }	
	
}

function display_Good_Msg($message) {
	
$htmlCode = <<<HCODE
	<div class="container">
		<div class="col-sm-6 col-sm-offset-3 col-xs-12">
			<div class="alert alert-success" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
				$message
			</div>
		</div>
	</div>
HCODE;
	
	echo $htmlCode;
	
}

function display_Bad_Msg($message) {
	
$htmlCode = <<<HCODE
	<div class="container">
		<div class="col-sm-6 col-sm-offset-3 col-xs-12">
			<div class="alert alert-danger" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
				$message
			</div>
		</div>
	</div>
HCODE;
	
	echo $htmlCode;
	
}

function display_Info_Msg($message) {
	
$htmlCode = <<<HCODE
	<div class="container">
		<div class="col-sm-6 col-sm-offset-3 col-xs-12">
			<div class="alert alert-info" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
				$message
			</div>
		</div>
	</div>
HCODE;
	
	echo $htmlCode;
	
}

function echo_header($myFile) {
	
$htmlCode = <<<HCODE
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>Leave A Tip</title>

		<!-- Bootstrap -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!-- Extra CSS -->
		<link href="css/styles.css" rel="stylesheet">

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

		<script>
			// Run after document load. http://api.jquery.com/ready/
			$(function() {

				// Fade in the page on load
				$("body").fadeIn(2700);

				// Set Unload of page event
				window.addEventListener('beforeunload', function() { // Not working all the time ?? Got it to fade out only a few times.
					console.log("Unloading...");
					$("body").fadeOut(2000);
				}, false);

			});
		</script>
	</head>

	<body>
	<div class='well row'>
	<div class='col-xs-6'><a href='$myFile'>PHP Tip Calculator Example</a></div>
	
	</div>
HCODE;
	
	echo $htmlCode;
	
}

function display_Calculator($myFile) {
	
$htmlCode = <<<HCODE
	<div class="container">
		<div class="col-sm-6 col-sm-offset-3 col-xs-12">
			<div class="panel panel-primary">
				<div class="panel-heading">
					Calculate Tip</a>
				</div>
				<div class="panel-body">

					<form action="$myFile" method="POST" class="form-horizontal">
						<div class="form-group form-group-lg">
							<label for="amountBill" class="control-label col-xs-2 col-sm-3">Bill Amount:</label>
							<div class="col-sm-9">
								<input name="amountBill" id="amountBill" class="form-control" type="text" placeholder="$0.00">
							</div>
						</div>
						<div class="form-group form-group-lg">
							<label for="amountTip" class="control-label col-xs-2 col-sm-3">Tip Percent:</label>
							<div class="col-sm-9">
								<input name="amountTip" id="amountTip" class="form-control" type="text" placeholder='0%'>
							</div>
						</div>
						<div class="form-group form-group-lg ">
							<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-push-2">
								<button name="btnCalculate" type="submit" class="bn btn-sm btn-primary btn-block">Calculate</button>
							</div>
						</div>


					</form>

				</div>
			</div>
		</div>
	</div>
HCODE;

	echo $htmlCode;

}


function display_history() {

	// Include the DB connection details
	include_once("db.php");
	
$htmlCode1 = <<<HCODE1
	<div class="container">
		<div class="col-sm-6 col-sm-offset-3 col-xs-12">
			<div class="panel panel-default" id="historyTable">
				<div class="panel-heading">
					Calculation History
					
					<button type="button" class="close" data-target="#historyTable" data-dismiss="alert"> <span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				</div>
				<div class="panel-body">

					<div class="table-responsive">
						<table class="table table-striped ">
							<thead>
								<tr>
									<th>#</th>
									<th>Date</th>
									<th>Original Bill Amount</th>
									<th>Tip Amount</th>
									<th>Grand Total Bill</th>
								</tr>
							</thead>
							<tbody>
HCODE1;

	
	// Get rows from the DB
	try {
		$statement = $link->prepare("SELECT * FROM " . $dbDetails["tablename"] . " ORDER BY billdate DESC");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		$rowCode="";

		foreach ($result as $row) {
			$rowCode .= "<tr>";
			$rowCode .= "<td class='text-right'>" . $row['billid'] . "</td>";
			$rowCode .= "<td class=''>" . $row['billdate'] . "</td>";
			$rowCode .= "<td class='text-right'>$" . number_format($row['billamount'], 2) . "</td>";
			$rowCode .= "<td class='text-right'>$" . number_format($row['tipamount'], 2) . "</td>";
			$rowCode .= "<td class='text-right'>$" . number_format($row['totalamount'], 2) . "</td>";
			$rowCode .= "</tr>";	
		}

		// Close Connection
		$statement=null;
		closeLink();

	} catch (PDOException $e) {

		if ($i == 1) {
			// First time ? retry
			connectLink();
		} else {
			// Second time, KO
			$statement = "(unknown)";
			echo 'PDO Connection failed: ' . $e->getMessage().'. ';
		}
	}
	



$htmlCode2 = <<<HCODE2
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>	
HCODE2;
	
	echo $htmlCode1 . $rowCode . $htmlCode2;	
	
}


function echo_footer($myFile) {
	
	echo "<div class='well row'>";
	echo "<div class='col-xs-6'><a href='$myFile'>PHP Tip Calculator Example</a></div>";
	echo "<div class='col-xs-6 text-right'>Personal homepage: <a href='http://SethLeedy.Name'>Seth Leedy.Name</a></div>";
	echo "</div>";
	echo "</body></html>";
}


?>