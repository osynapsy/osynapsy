<?php
require realpath($argv[1].'/../vendor/autoload.php');

$cron = new \Osynapsy\Console\Cron($argv);
echo $cron->run();

