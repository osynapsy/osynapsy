<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

/**
 * Description of ChartEJS
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ChartGoogle extends Component
{
    private $columns = array();
    private $rows = array();
    private $type;
    private $options = array(
        'title' => 'No title'        
    );
    
    public function __construct($id,  $title = 'No title', $type='BarChart')
    {
        parent::__construct('div', $id);
        $this->type = $type;
        $this->att('class','OclChartGoogle');
        $this->setOption('title', $title);
        $this->requireJs('//www.gstatic.com/charts/loader.js');        
    }
    
    public function __build_extra__()
    {        
        $script = $this->add(new Tag('script'));
        $script->add("document.addEventListener('DOMContentLoaded',function() {".PHP_EOL);
        $script->add("google.charts.load('current', {'packages':['corechart']});".PHP_EOL);
        $script->add("google.charts.setOnLoadCallback(drawChart{$this->id});".PHP_EOL);
        $script->add("});".PHP_EOL);
        $script->add("function drawChart{$this->id}() {".PHP_EOL);
        $script->add('var options = '.$this->buildJsObject($this->options).';'.PHP_EOL);
        $script->add('var data = new google.visualization.DataTable();'.PHP_EOL);
        foreach ($this->columns as $name => $type) {
            $script->add("data.addColumn('$type','$name');".PHP_EOL);
        }
        $script->add('data.addRows(['.PHP_EOL.implode(','.PHP_EOL, $this->rows).']);'.PHP_EOL);        
        $script->add("var chart = new google.visualization.{$this->type}(document.getElementById('{$this->id}'));".PHP_EOL);
        $script->add("chart.draw(data, options);");        
        $script->add("}".PHP_EOL);
        
    }
    
    private function buildJsObject(array $array)
    {
        $properties = array();
        foreach ($array as $key => $value) {
            $properties[] = $key." : '".(is_string($value) ? addslashes($value) : $value)."'".PHP_EOL;
        }
        return '{'.implode(','.PHP_EOL,$properties).'}';
    }
    
    public function addColumn($name, $type = 'string')
    {
        $this->columns[$name] = $type;        
    }
    
    public function addRow(array $raw)
    {                    
        $row = array();
        foreach ($raw as $value) {
            $row[] = is_string($value) ? "'".addslashes(trim($value))."'" : trim($value);
        }
        $this->rows[] = "[".implode(',',$row)."]";
    }
    
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }
}
