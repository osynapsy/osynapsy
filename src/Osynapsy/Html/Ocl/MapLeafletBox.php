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

class MapLeafletBox extends Component
{
	private $map;
	private $datagridParent;
    
	public function __construct($name)
	{
		parent::__construct('dummy',$name);
		$this->requireCss('/__assets/osynapsy/Lib/leaflet-0.7.3/leaflet.css');
		$this->requireCss('/__assets/osynapsy/Lib/leaflet-0.7.3/leaflet.draw.css');
		$this->requireJs('/__assets/osynapsy/Lib/leaflet-0.7.3/leaflet.js');
		$this->requireJs('/__assets/osynapsy/Lib/leaflet-0.7.3/leaflet.awesome-markers.min.js');
		$this->requireJs('/__assets/osynapsy/Lib/leaflet-0.7.3/leaflet.draw.js');
		$this->requireJs('/__assets/osynapsy/Ocl/MapLeafletBox/script.js');

		$this->map = $this->add(new Tag('div'))
                          ->att('id',$name)
                          ->att('style','width: 100%; min-height: 600px;')
                          ->att('class','osy-mapgrid-leaflet');
		$this->add(new HiddenBox($this->id.'_ne_lat'));
        $this->add(new HiddenBox($this->id.'_ne_lng'));
        $this->add(new HiddenBox($this->id.'_sw_lat'));
        $this->add(new HiddenBox($this->id.'_sw_lng'));
        $this->add(new HiddenBox($this->id.'_center'));
  	    $this->add(new HiddenBox($this->id.'_cnt_lat'));
        $this->add(new HiddenBox($this->id.'_cnt_lng'));
		$this->add(new HiddenBox($this->id.'_zoom'));
	}
	
	public function __build_extra__()
	{
		
        /*foreach($this->att as $k => $v) {
         
			if (is_numeric($k)) {
                continue;
            }
			$this->map->att($k, $v, true);
		}*/        
		if (empty($res)){ 
		  	$res = array(
                array(
                    'lat'=>41.9100711,
                    'lng'=>12.5359979
                )
            );	
		}
        $coordinateStart = $res[0]['lat'].','.$res[0]['lng'];
        $coordinateStart .= isset($res[0]['ico']) ? ','.$res[0]['ico'] : '';
		$this->map->att('coostart', $coordinateStart);
		if (empty($_REQUEST[$this->id.'_center'])) {
			$_REQUEST[$this->id.'_center'] = $res[0]['lat'].','.$res[0]['lng'];
		}
        
        $this->map->att('data-datagrid-parent', '#'.$this->datagridParent);        
	}
    
    public function setGridParent($gridName)
    {
        $this->datagridParent = $gridName;
    }
}
