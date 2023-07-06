<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Action\AbstractAction;

/**
 * Description of ModelDelete action;
 *
 * @author Pietro
 */
class Delete extends AbstractAction
{
    public function __construct()
    {
        $this->setTrigger(['afterDelete'], [$this, 'afterDelete']);
    }

    public function execute()
    {
        try {
            $this->executeTrigger('beforeDelete');
            $this->getModel()->delete();
            $this->executeTrigger('afterDelete');
        } catch(\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
        }
    }

    public function afterDelete()
    {
        $this->getResponse()->go('back');
    }
}
