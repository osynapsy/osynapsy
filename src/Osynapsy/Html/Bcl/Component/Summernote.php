<?php
namespace Osynapsy\Bcl\Component;

class Summernote extends TextArea
{
    public function __construct($name)
    {
        parent::__construct($name);        
        $this->att('class','summernote');
        $this->requireCss('/__assets/osynapsy/Lib/summernote-0.8.2/summernote.css');
        $this->requireJs('/__assets/osynapsy/Lib/summernote-0.8.2/summernote.js');
        $this->requireJs('/__assets/osynapsy/Bcl/Summernote/script.js');  
    }    
}
