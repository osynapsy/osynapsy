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

use Osynapsy\Html\Component as OclComponent;
use Osynapsy\Html\Tag;

/**
 * Build a card
 * 
 */
class Card extends OclComponent
{
    private $head;
    
    public function __construct($name, $title = null, array $commands = [])
    {
        parent::__construct('div',$name);
        $this->att('class','card');
        $this->head  = new Tag('div');
        $this->head->att('class','card-header ch-alt clearfix');
        if (!empty($title)) {
            $this->head->add('<h2 class="pull-left">'.$title.'</h2>');
        }
        $this->buildCommandContainer($commands);
        if (!empty($title) || !empty($commands)) {
            $this->add($this->head);
        }
    }
    
    private function buildCommandContainer($commands)
    {
        if (empty($commands)) {
            return;
        }
        $commandContainer = new Tag('div');
        $commandContainer->att('class', 'pull-right m-t-sm');
        foreach($commands as $command) {
            $commandContainer->add($command);
        }
        $this->head->add($commandContainer);
    }
}
