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
	</style>
</head>
<body>
	<div class="row">
		<div class="column"><?php include("../evernote.php"); ?></div>
		<div class="column"><?php include_once("../calendar.php"); todayEvents(); ?></div>
	</div>
	<div id="footer">
		<?php echo date("m/d/Y g:i:s"); ?> 1.php
	</div>
</body>
</html>
