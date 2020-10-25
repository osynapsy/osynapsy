<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Bcl\TextArea;

class Summernote extends TextArea
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','summernote');
        $this->requireCss('Lib/summernote-0.8.18/summernote-bs4.css');
        $this->requireJs('Lib/summernote-0.8.18/summernote-bs4.js');
        $this->requireJs('Bcl/Summernote/script.js');
    }

    public function setHeight(int $heightInPixel)
    {
        $this->att('data-height', $heightInPixel);
    }
}
