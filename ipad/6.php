<html>
<head>
	<link href="style.css" type="text/css" rel="stylesheet">
	<style type="text/css">
	.column {
		float: left;
		width: 50%;
		background-color: rgba(0,0,0,0);
	}

	/* Clear floats after the columns */
	.row:after {
		content: "";
		display: table;
		clear: both;
	}

	body {
		background-color: #04243A;
	}
	</style>
</head>
<body>
	<?php include_once("../calendar.php"); todayEvents(); include_once("../aws.php"); showAlerts(); include("../wunderlist.php");?>
</body>
</html>
