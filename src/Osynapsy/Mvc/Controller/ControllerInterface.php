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
use Osynapsy\Mvc\Model\InterfaceModel;
use Osynapsy\Database\Driver\InterfaceDbo;

interface ControllerInterface
{
    public function __construct(Request $request = null, ApplicationInterface $application = null);

    public function getApp() : ApplicationInterface;

    public function getDb() : InterfaceDbo;

    public function getDispatcher();

    public function getModel() : InterfaceModel;

    public function getResponse();

    public function getRequest();

    public function setExternalAction(string $actionId, ActionInterface $actionClass) : void;

    public function setModel(InterfaceModel $model);

    public function setView(InterfaceView $view);

    public function run($action, $parameters = []);
}