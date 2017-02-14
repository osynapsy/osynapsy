<?php
namespace Osynapsy\Ocl\Component;
/*
 +-----------------------------------------------------------------------+
 | Osynapsy\Ocl\Componen\GMapBox.php                                     |
 |                                                                       |
 | This file is part of the Osynapsy                                     |
 | Copyright (C) 2005-2013, Pietro Celeste - Italy                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create page form for generate GMapBox                               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Pietro Celeste <pietro.celeste@gmail.com>                     |
 +-----------------------------------------------------------------------+

 $Id:  $

/**
 * @email           pietro.celeste@osynapsy.org
 * @date-creation   31/10/2014
 * @date-update     31/10/2014
 */
 

class MapGoogleBox extends Component
{
    private $map;
    
    public function __construct($name)
    {
        parent::__construct('dummy',$name);
        $this->requireCss('/__assets/osynapsy/Ocl/GMapBox/style.css');
        $this->requireJs('http://maps.google.com/maps/api/js?sensor=false&amp;language=en&libraries=drawing');
        $this->requireJs('/__assets/osynapsy/Lib/gmap3-6.0.0/gmap3.min.js');
        $this->requireJs('/__assets/osynapsy/Ocl/GMapBox/script.js');
        $this->map = $this->add(new Tag('div'))->att('class','osy-mapgrid');
        $this->add(new HiddenBox($this->id.'_ne_lat'));
        $this->add(new HiddenBox($this->id.'_ne_lng'));
        $this->add(new HiddenBox($this->id.'_sw_lat'));
        $this->add(new HiddenBox($this->id.'_sw_lng'));
        $this->add(new HiddenBox($this->id.'_center'));
        $this->add(new HiddenBox($this->id.'_polygon'));
        $this->add(new HiddenBox($this->id.'_zoom'));
        $this->add(new HiddenBox($this->id.'_refresh_bounds_blocked'));
    }
    
    public function __build_extra__()
    {
        foreach ($this->get_att() as $k => $v) {
            if (is_numeric($k)) continue;
            $this->map->att($k, $v, true);
        }
        if (empty($_REQUEST[$this->id.'_center'])) {
            $res = array(array('lat'=>41.9100711,'lng'=>12.5359979));
            $_REQUEST[$this->id.'_center'] = $res[0]['lat'].','.$res[0]['lng'];
        }
        if ($grid = $this->get_par('datagrid-parent')) {
            $this->map->att('data-datagrid-parent',$grid);
        }
    }
}

