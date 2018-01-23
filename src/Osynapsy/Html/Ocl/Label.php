<?php
namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Component;

class Label extends Component
{
    public function __construct($name)
    {
        parent::__construct('label',$name);
        $this->att('class','normal');
        $this->add(new hidden_box($name));
    }
    
    protected function __build_extra__()
    {
        $val = get_global($this->id,$_REQUEST);
        if ($pointer = $this->get_par('global-pointer'))
        {
            $ref = array(&$GLOBALS,&$_REQUEST,&$_POST);
            foreach ($ref as $global_arr)
            {
                if (key_exists($pointer,$global_arr))
                {
                    $val = $global_arr[$pointer];
                    break;
                }
            }
        }
		if (strstr($val,"\n")){
			$this->add(nvl('<pre>'.$val.'</pre>','&nbsp;'));
		} else {
        	$this->add(nvl($val,'&nbsp;'));
		}
    }        
}