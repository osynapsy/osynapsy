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

use Osynapsy\Html\Component; 

class MapGoogleBox extends Component
{
    private $map;
    
    public function __construct($name)
    {
        parent::__construct('dummy',$name);
        $this->requireCss('Ocl/GMapBox/style.css');
        $this->requireJs('//maps.google.com/maps/api/js?sensor=false&amp;language=en&libraries=drawing');
        $this->requireJs('Lib/gmap3-6.0.0/gmap3.min.js');
        $this->requireJs('Ocl/GMapBox/script.js');
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
        if ($grid = $this->getParameter('datagrid-parent')) {
            $this->map->att('data-datagrid-parent',$grid);
        }
    }
}

