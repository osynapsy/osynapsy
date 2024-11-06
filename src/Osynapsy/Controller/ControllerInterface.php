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
use Osynapsy\Application\ApplicationInterface;
use Osynapsy\Database\Driver\DboInterface;
use Osynapsy\Html\Helper\JQuery;

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

    public function getExternalAction(string $actionId) : string;

    public function getResponse() : ResponseInterface;

    public function getRequest() : Request;

    public function hasExternalAction($actionId) : bool;

    public function hasDb() : bool;

    public function setExternalAction(string $actionClass) : void;

    public function alert($message) : void;

    public function closeModal() : void;

    public function modalWindow(string $title, string $url, string $width = '640px', string $height = '480px') : void;

    public function modalConfirm(string $message, string $actionOnConfirm, string $title = 'Confirm') : void;

    public function modalAlert(string $message, string $title = 'Alert') : void;

    public function refreshComponents(array $componentIds) : void;

    public function refreshParentComponents(array $componentIds) : void;

    public function js($jscode) : void;

    public function jquery($jquerySelector) : JQuery;

    public function go($destination) : void;

    public function historyPushState($id) : void;
}
