<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

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
