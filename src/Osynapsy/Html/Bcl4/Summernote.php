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
        self::appendRequiredFileToPage();
    }

    public function setHeight(int $heightInPixel)
    {
        $this->att('data-height', $heightInPixel);
    }

    public static function appendRequiredFileToPage()
    {
        self::requireCss('Lib/summernote-0.8.18/summernote-bs4.css');
        self::requireJs('Lib/summernote-0.8.18/summernote-bs4.js');
        self::requireJs('Bcl/Summernote/script.js');
    }

    public function showFontButtons($superscript = false, $subscript = false, $strikethrough = false)
    {
        $buttons = [];
        if ($strikethrough === true) {
            $buttons[] = 'font-strikethrough';
        }
        if ($superscript === true) {
            $buttons[] = 'font-superscript';
        }
        if ($subscript === true) {
            $buttons[] = 'font-subscript';
        }
        $this->addButtonsToToolbar($buttons);
    }

    public function addButtonsToToolbar(array $newbuttons)
    {
        if (empty($newbuttons)) {
            return;
        }
        $oldbuttons = $this->getAttribute('data-toolbar-buttons') ? explode(',',  $this->getAttribute('data-toolbar-buttons')) : [];
        $buttons = array_merge($oldbuttons, $newbuttons);
        $this->att('data-toolbar-buttons', implode(',', $buttons));
    }
}
