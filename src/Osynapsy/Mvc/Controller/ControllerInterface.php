<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Controller;

use Osynapsy\Http\Request;
use Osynapsy\Mvc\Action\ActionInterface;
use Osynapsy\Mvc\Application\ApplicationInterface;
use Osynapsy\Mvc\Model\ModelInterface;
use Osynapsy\Database\Driver\DboInterface;

/**
 * Controller Interface
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
interface ControllerInterface
{
    public function __construct(Request $request = null, ApplicationInterface $application = null);

    public function getApp() : ApplicationInterface;

    public function getDb() : DboInterface;

    public function getDispatcher();

    public function getModel() : ModelInterface;

    public function getResponse();

    public function getRequest();

    public function setExternalAction(string $actionId, ActionInterface $actionClass) : void;

    public function setModel(ModelInterface $model);

    public function setView(InterfaceView $view);

    public function hasExternalAction($actionId);

    public function hasModel();
}
