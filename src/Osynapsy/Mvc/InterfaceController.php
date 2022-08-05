<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

use Osynapsy\Http\Request;
use Osynapsy\Mvc\Action\InterfaceAction;
use Osynapsy\Mvc\Application\InterfaceApplication;
use Osynapsy\Db\Driver\InterfaceDbo;

interface InterfaceController
{
    public function __construct(Request $request = null, InterfaceApplication $application = null);

    public function getApp() : InterfaceApplication;

    public function getDb() : InterfaceDbo;

    public function getDispatcher();

    public function getModel() : InterfaceModel;

    public function getResponse();

    public function getRequest();

    public function setExternalAction(string $actionId, InterfaceAction $actionClass) : void;

    public function setModel(InterfaceModel $model);

    public function setView(InterfaceView $view);

    public function run($action, $parameters = []);
}