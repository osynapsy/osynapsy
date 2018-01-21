<?php
$autoload = realpath($argv[1].'/../vendor/autoload.php');
require $autoload;
require 'Lib/Cron.php';

$cron = new Cron($argv);
echo $cron->run();

