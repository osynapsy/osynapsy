<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component as OclComponent;
use Osynapsy\Html\Tag;

/**
 * Build a card
 * 
 */
class Card extends OclComponent
{
    public function __construct($name, $title=null)
    {
        parent::__construct('div',$name);
        $this->att('class','card');
        if (!empty($title)) {
            $this->add(new Tag('div'))
                 ->att('class','card-header ch-alt')
                 ->add('<h2>'.$title.'</h2>');
        }
    }
}
