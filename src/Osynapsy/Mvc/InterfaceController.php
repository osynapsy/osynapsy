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
use Osynapsy\Mvc\Application;

interface InterfaceController
{
    public function __construct(Request $request = null, Application $application = null);

    public function getDb();

    public function getDispatcher();

    public function getModel() : InterfaceModel;

    public function getResponse();

    public function getRequest();

    public function setModel(InterfaceModel $model);

    public function setView(InterfaceView $view);

    public function run($action, $parameters = []);
}