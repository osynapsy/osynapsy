<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

chdir(__DIR__);

require realpath($argv[1].'/../vendor/autoload.php');

$cron = new \Osynapsy\Console\Cron($argv);
echo $cron->run();

