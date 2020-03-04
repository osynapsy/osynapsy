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
    
    protected $rawId;
    protected $hiddenBox;
    protected $message;
    protected $type;
    protected $isDismissible = false;
    protected $icon = '';
    
    public function __construct($id, $message, $type = self::ALERT_INFO)
    {
        parent::__construct('div', $id.'_container');
        $this->rawId = $id;
        $this->hiddenBox = $this->add(new HiddenBox($id));
        $this->att('class','alert')->att('role','alert');        
        $this->setMessage($message);
        $this->setType($type);
    }
    
    public function __build_extra__(): void
    {
        $this->setClass("alert-{$this->type}");
        $this->add(sprintf("%s<span id=\"%s\">%s</span>", $this->icon, $this->rawId.'_label', $this->message));
        if (!$this->isDismissible) {
            return;
        }
        $this->setClass('alert-dismissible text-center');
        $this->add(' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
    }
    
    public function setDismissible($condition)
    {
        $this->isDismissible = $condition;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function showIcon($condition)
    {   
        if (!$condition) {
            $this->icon = '';
            return;
        }
        switch ($this->type) {
            case 'danger':
                $this->icon = '<span class="fa fa-exclamation-triangle" aria-hidden="true"></span><span class="sr-only">Error:</span> ';
                break;
        }
    }
}