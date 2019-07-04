<?php

// Get sensitive values
$ini = parse_ini_file("../config.ini", true)["cc"];

require_once("vendor/autoload.php");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(-1);
error_reporting(0);

$conn = mysqli_connect("76.97.57.153", $ini['db_user'], $ini['db_password'], "CommandCenter");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

date_default_timezone_set("America/New_York");

// Start session if not already created
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('MODE_NEW', 'new');
define('MODE_EDIT', 'edit');

// Steralize input (remove crazy characters)
function steralizeString($str)
{
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}

function devEnv()
{
    return gethostname() == "Ryans-MBP";
}
