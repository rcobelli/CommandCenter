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
	<div class="row">
		<div class="column"><?php include("../weather.php"); include("../wunderlist.php"); include_once("../calendar.php"); todayEvents();?></div>
		<div class="column"><?php include_once("../aws.php"); showMonitoring();?></div>
	</div>
</body>
</html>
