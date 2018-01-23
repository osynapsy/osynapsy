<?php
namespace Osynapsy\Ocl\Component;
/*
 +-----------------------------------------------------------------------+
 | lib/components/omapgrid.php                                           |
 |                                                                       |
 | This file is part of the Opensymap                                    |
 | Copyright (C) 2005-2013, Pietro Celeste - Italy                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create page form for generate datagrid and treegrid                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Pietro Celeste <pietro.celeste@gmail.com>                     |
 +-----------------------------------------------------------------------+

 $Id:  $

/**
 * @email           pietro.celeste@opensymap.org
 * @date-creation   31/10/2014
 * @date-update     31/10/2014
 */
 
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
		$this->map->att('coostart', $res[0]['lat'].','.$res[0]['lng'].','.$res[0]['ico']);
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
