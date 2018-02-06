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

class Slider extends Component 
{
	public function __construct($id)
    {
        parent::__construct('div', $id);
		$this->att('class', 'osy-slider');
		$this->requireJs('Ocl/Slider/script.js');
    }
	
	protected function __build_extra__()
    {
		if ($range = $this->getParameter('slider-range')){
			$this->att('data-range',$range);
            $this->add(new hidden_box($this->id.'_min'));
            $this->add(new hidden_box($this->id.'_max'));
		} else {
            $this->add(new hidden_box($this->id));
        }
		$min = $this->getParameter('min');
		$div_min_max = $this->add(tag::create('div'));
		$div_min_max->att('class','osy-slider-min-max');
		$div_min_max->add('&nbsp');
		if ($min == '0' or !empty($min)){
			if ($min[0] == '$'){ eval('$min = '.$min.';'); } 
			$div_min_max->add('<span class="lbl-min">'.$min.'</span>'); 
			$this->att('data-min',$min);
		}
		$bar = $this->add(tag::create('div'))->att('class','osy-slider-bar');
		if ($max = $this->getParameter('max')){
			if ($max[0] == '$'){ eval('$max = '.$max.';'); } 
			$div_min_max->add('<span class="lbl-max">'.$max.'</span>'); 
			$this->att('data-max',$max);
		}
		if (!empty($_REQUEST[$this->id.'_min']) && 
			!empty($_REQUEST[$this->id.'_max'])){
		    $this->att('data-values',$_REQUEST[$this->id.'_min'].','.$_REQUEST[$this->id.'_max']);
		}
		$this->add('<script>
		oslider.onevent("onstop","'.$this->id.'",function(event,ui){
		'.$this->getParameter('onstop').'
		});
		</script>');
		//$this->add('<span class="osy-slider-result"></span>');
	}
}
