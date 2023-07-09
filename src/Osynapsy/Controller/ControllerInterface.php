<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Controller;

use Osynapsy\Http\Request;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Action\ActionInterface;
use Osynapsy\Application\ApplicationInterface;
use Osynapsy\ViewModel\ModelInterface;
use Osynapsy\Database\Driver\DboInterface;

/**
 * Controller Interface
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
interface ControllerInterface
{
    public function __construct(Request $request, ApplicationInterface $application);

    public function getApp() : ApplicationInterface;

    public function getDb() : ?DboInterface;

    public function getDispatcher();

    public function getModel() : ModelInterface;

    public function getExternalAction(string $actionId) : ActionInterface;

    public function getResponse() : ResponseInterface;

    public function getRequest() : Request;

    public function hasExternalAction($actionId) : bool;

    public function hasDb() : bool;

    public function hasModel() : bool;

    public function setExternalAction(string $actionClass) : void;

    public function setModel(ModelInterface $model);
}
