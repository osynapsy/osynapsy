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
        self::appendRequiredFileToPage();
    }

    public function setHeight(int $heightInPixel)
    {
        $this->att('data-height', $heightInPixel);
    }

    public static function appendRequiredFileToPage()
    {
        self::requireCss('Lib/summernote-0.8.18/summernote.css');
        self::requireJs('Lib/summernote-0.8.18/summernote.js');
        self::requireJs('Bcl/Summernote/script.js');
    }

    public function setAction($action, $parameters = null, $class = 'upload-execute', $confirmMessage = null): \this
    {
        parent::setAction($action, $parameters, $class, $confirmMessage);
    }
}
