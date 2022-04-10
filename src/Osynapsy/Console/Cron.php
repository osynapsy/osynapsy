<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Console;

use Osynapsy\Kernel\Loader;
use Osynapsy\Kernel\Route;
use Osynapsy\Kernel;

/**
 * Description of Cron
 *
 * @author Peter
 */
class Cron
{
    private $argv;
    private $script;
    private $kernel;

    public function __construct(array $argv)
    {
        $this->script = array_shift($argv);
        $this->argv = $argv;
    }

    public function run()
    {
        $appConfiguration = $this->loadAppConfiguration($this->argv[0]);
        $cronJobs = $this->getCronJobs($appConfiguration);
        if (!empty($cronJobs)) {
            $this->exec($cronJobs);
        }
    }

    private function loadAppConfiguration($instancePath)
    {
        if (!is_dir($instancePath)) {
            return;
        }
        $loader = new Loader($instancePath);
        return $loader->search('app');
    }

    private function getCronJobs($configuration)
    {
        if (empty($configuration) || !is_array($configuration)) {
            return;
        }
        $jobs = [];
        foreach($configuration as $app => $config) {
            if (!empty($config['cron'])) {
                $jobs[$app] = $config['cron'];
            }
        }
        return $jobs;
    }

    private function exec($jobs)
    {
        $this->kernel = new Kernel($this->argv[0]);
        foreach($jobs as $appId => $appJobs) {
            foreach($appJobs as $jobId => $jobController){
                $this->execJob($jobId , $appId, $jobController);
            }
        }
    }

    private function execJob($jobId, $application, $controller)
    {
        $job = new Route($jobId, null, $application, $controller);
        echo $this->kernel->followRoute($job);
    }
}
