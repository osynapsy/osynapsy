<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;

class InputGroup extends Component
{
    protected $textBox;
    
    public function __construct($name, $prefix = '', $postfix = '')
    {
        parent::__construct('div');
        $this->att('class','input-group');
        if (!empty($prefix)) {
            $this->add(new Tag('span'))
                 ->att('class', 'input-group-addon')
                 ->att('id',$name.'_prefix')
                 ->add($prefix);
        }
        if (is_object($name)) {
            $this->textBox = $this->add($name);
        } else {
            $this->textBox = $this->add(new TextBox($name));
            $this->textBox->att('aria-describedby',$name.'_prefix');
        }
        
        if ($postfix) {
            $this->add(new Tag('span'))
                 ->att('class', 'input-group-addon')
                 ->add($postfix);
        }
    }
    
    public function getTextBox()
    {
        return $this->textBox;
    }    
}
