<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Bcl\TextBox;

/**
 * Description of Password
 *
 * @author Pietro
 */
class PasswordBox extends TextBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setClass('text-security');
        $this->requireCss('assets/Bcl4/PasswordBox/text-security.css');
    }
}
