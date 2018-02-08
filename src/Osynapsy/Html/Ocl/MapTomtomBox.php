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

class MapTomtomBox extends Component
{
	private $map;
	private $datagridParent;
    
	public function __construct($name)
	{
		parent::__construct('dummy',$name);        
		$this->requireCss('/sdk/map.css');		   				
		$this->requireJs('/sdk/tomtom.min.js');
        $this->requireCss('Ocl/MapTomtomBox/style.css');
        $this->requireJs('Ocl/MapTomtomBox/script.js');
        $this->setParameter('center', ['lat'=>41.9100711, 'lng'=>12.5359979]);
        $this->includeAwesomeMarkersPlugin();
		$this->map = $this->add(new Tag('div'))
                          ->att('id',$name)
                          ->att('style','width: 100%; min-height: 600px;')
                          ->att('class','osy-mapgrid osy-mapgrid-tomtom');
		$this->add(new HiddenBox($this->id.'_ne_lat'));
        $this->add(new HiddenBox($this->id.'_ne_lng'));
        $this->add(new HiddenBox($this->id.'_sw_lat'));
        $this->add(new HiddenBox($this->id.'_sw_lng'));
        $this->add(new HiddenBox($this->id.'_center'));
  	    $this->add(new HiddenBox($this->id.'_cnt_lat'));
        $this->add(new HiddenBox($this->id.'_cnt_lng'));
		$this->add(new HiddenBox($this->id.'_zoom'));
	}
	
    private function includeAwesomeMarkersPlugin()
    {
        $this->requireCss('Lib/leaflet-awesome-markers-2.0.1/leaflet.awesome-markers.css');
		$this->requireJs('Lib/leaflet-awesome-markers-2.0.1/leaflet.awesome-markers.min.js');		
    }
    
	public function __build_extra__()
	{		              
		$defaultCenter = $this->getParameter('center');
        if (empty($_REQUEST[$this->id.'_center'])) {
			$_REQUEST[$this->id.'_center'] = $defaultCenter['lat'].','.$defaultCenter['lng'];
		}
        $coordinateStart = implode(',', array_values($this->getParameter('center')));        
		$this->map->att('data-marker', $coordinateStart);		        
        $this->map->att('data-datagrid-parent', '#'.$this->datagridParent);        
	}
    
    public function setGridParent($gridName)
    {
        $this->datagridParent = $gridName;
    }
    
    public function setStartPoint($lat, $lng, $marker = 'fa-building')
    {
        $this->setParameter('center', ['lat' => $lat, 'lng' => $lng, 'marker' => $marker]);
    }        
}
