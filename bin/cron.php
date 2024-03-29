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

$composerAutoloadPath = realpath($argv[1].'/../vendor/autoload.php');

if ($composerAutoloadPath === false) {
    die(sprintf('Non sono riuscito a trovare l\'autoload composer in %s', $argv[1]));
}

require $composerAutoloadPath;

$cron = new \Osynapsy\Console\Cron($argv);
echo $cron->run();
