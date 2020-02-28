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
    
    private function load($configuration)
    {
        if (empty($configuration) || !is_array($configuration)) {
            return;
        }
        $jobs = [];
        foreach($configuration as $app => $config) {
            if (empty($config['cron'])) {
                continue;
            }
            $jobs[$app] = $config['cron'];
        }
        return $jobs;
    }
    
    private function loadConfiguration()
    {
        if (!is_dir($this->argv[0])) {
            return;
        }
        $loader = new Loader($this->argv[0]);
        return $loader->search('app');
    }
    
    public function run()
    {
        $this->exec(
            $this->load(
                $this->loadConfiguration()
            )
        );
    }
    
    private function exec($jobs)
    {
        if (empty($jobs)) {
            return;
        }
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
