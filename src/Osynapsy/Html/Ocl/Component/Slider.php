<?php 
namespace Osynapsy\Html\Ocl\Component;

use Osynapsy\Html\Component;

class Slider extends Component 
{
	public function __construct($id)
    {
        parent::__construct('div', $id);
		$this->att('class', 'osy-slider');
		$this->requireJs('/__assets/osynapsy/OclSlider/script.js');
    }
	
	protected function __build_extra__()
    {
		if ($range = $this->get_par('slider-range')){
			$this->att('data-range',$range);
            $this->add(new hidden_box($this->id.'_min'));
            $this->add(new hidden_box($this->id.'_max'));
		} else {
            $this->add(new hidden_box($this->id));
        }
		$min = $this->get_par('min');
		$div_min_max = $this->add(tag::create('div'));
		$div_min_max->att('class','osy-slider-min-max');
		$div_min_max->add('&nbsp');
		if ($min == '0' or !empty($min)){
			if ($min[0] == '$'){ eval('$min = '.$min.';'); } 
			$div_min_max->add('<span class="lbl-min">'.$min.'</span>'); 
			$this->att('data-min',$min);
		}
		$bar = $this->add(tag::create('div'))->att('class','osy-slider-bar');
		if ($max = $this->get_par('max')){
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
		'.$this->get_par('onstop').'
		});
		</script>');
		//$this->add('<span class="osy-slider-result"></span>');
	}
}
