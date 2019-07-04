<?php

include_once("init.php");

session_destroy();
setcookie("commandcenter", null, 1, '/');
header("Location: index.php");
