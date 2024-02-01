<?php

include_once("../../init.php");

$config['type'] = Rybel\backbone\LogStream::cron;

(new SystemHelper($config))->garbageCollect();
(new CronHelper($config))->garbageCollect();
