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

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;

class Alert extends Component
{
    const ALERT_INFO = 'info';
    const ALERT_SUCCESS = 'success';
    const ALERT_DANGER = 'danger';
    const ALERT_WARNING = 'warning';
    
    protected $hiddenBox;
    
    public function __construct($id, $value, $type = self::ALERT_INFO)
    {
        parent::__construct('div', $id.'_label');
        $this->hiddenBox = $this->add(new HiddenBox($id));
        $this->att('class','alert alert-'.$type)
             ->att('role','alert')
             ->add($value);
    }
}