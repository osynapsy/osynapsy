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
class ChartEJS extends Component
{
    private $serie;
    private $option = array(
        'title' => 'No title',
        //'legend_state' => 'minized',
        'show_legend' => false,
        'auto_resize' => true
    );
    public function __construct($id, $width=640, $height=480)
    {
        parent::__construct('div', $id);
        $this->att('class','OclChartEJS')
             ->att('style','width: '.$width.'px; height: '.$height.'px');
        $this->requireCss('Lib/ejscharts-2.1.3/EJSChart.css');
        $this->requireJs('Lib/ejscharts-2.1.3/EJSChart.min.js');        
    }
    
    public function __build_extra__()
    {
        $options = $this->buildJsObject($this->option);
        $script = $this->add(new Tag('script'));
        $script->add("document.addEventListener('DOMContentLoaded',function() {".PHP_EOL);
        $script->add('var chart'.$this->id.' = new EJSC.Chart("'.$this->id.'",'.$options.');'.PHP_EOL);
        $script->add('chart'.$this->id.'.addSeries(new EJSC.BarSeries(new EJSC.ArrayDataHandler([[1,6],[2,2],[3,3],[4,2],[5,3]])));'.PHP_EOL);      
        $script->add("});".PHP_EOL);
    }
    
    private function buildJsObject(array $array)
    {
        $properties = array();
        foreach ($array as $key => $value) {
            $properties[] = $key." : '".(is_string($value) ? addslashes($value) : $value)."'".PHP_EOL;
        }
        return '{'.implode(','.PHP_EOL,$properties).'}';
    }
    
    public function addSerie(array $serie)
    {            
        $strSerie = array();
        foreach ($serie as $key => $value) {
            $strSerie[] = "['$key',$value]";
        }
        $this->series[] = '['.implode(',',$serie).']';
    }
    
    public function setOption($key, $value)
    {
        $this->option[$key] = $value;
    }
}
