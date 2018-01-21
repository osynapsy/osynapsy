<?php

use Osynapsy\Core\Kernel\Loader;
use Osynapsy\Core\Kernel;

/**
 * Description of Cron
 *
 * @author Peter
 */
class Cron 
{
    private $argv;
    private $script;

    public function __construct(array $argv)
    {
        $this->script = array_shift($argv);
        $this->argv = $argv;
    }
    
    public function run()
    {
        $jobs = $this->load($this->loadConfiguration());
        $this->exec($jobs);
        return print_r($cron, true);
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
    
    private function exec($jobs)
    {
        if (empty($jobs)) {
            return;
        }
        $Kernel = new Kernel($this->argv[0]);
        foreach($jobs as $appId => $appJobs) {            
            foreach($appJobs as $jobId => $jobUri){
                ob_start();
                echo $Kernel->run($jobUri);
                ob_end_flush();
            }
        }
    }
    
    private function loadConfiguration()
    {
        if (!is_dir($this->argv[0])) {
            return;
        }
        $loader = new Loader($this->argv[0]);
        return $loader->search('app');
    }
}
