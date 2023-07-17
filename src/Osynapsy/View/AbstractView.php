<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\View;

use Osynapsy\Kernel;
use Osynapsy\ViewModel\ModelInterface;
use Osynapsy\Html\DOM;
use Osynapsy\Html\Tag;

abstract class AbstractView implements ViewInterface
{
    protected $title;
    protected $model;
    protected $meta = [];

    public function __construct(?ModelInterface $model, array $properties = [])
    {
        if (!is_null($model)) {
            $this->setModel($model);
        }
        $this->setProperties($properties);
    }

    abstract public function factory();

    public function addCss($path)
    {
        DOM::addCss($path);
    }

    public function addCssLibrary($path)
    {
        DOM::addCss(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addJs($path)
    {
        DOM::requireJs($path);
    }

    public function addScript($code)
    {
        DOM::requireScript($code);
    }

    public function addJsLibrary($path)
    {
        DOM::requireJs(sprintf('/assets/osynapsy/%s/%s', Kernel::VERSION, $path));
    }

    public function addMeta($property ,$content)
    {
        $meta = new Tag('meta');
        $meta->attributes(['property' => $property, 'content' => $content]);
        $this->meta[] = $meta;
    }

    public function addStyle($style)
    {
        DOM::requireStyle($style);
    }

    public function getModel() : ModelInterface
    {
        return $this->model;
    }

    public function getDb()
    {
        return $this->getModel()->getDb();
    }

    public function getTitle() : string
    {
        return DOM::getTitle();
    }

    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function setProperties(array $properties = [])
    {
        foreach ($properties as $id => $value) {
            $this->{$id} = $value;
        }
        return $this;
    }

    public function setTitle(string $title)
    {
        DOM::setTitle($title);
    }

    public function __toString()
    {
        $requestComponentIDs = empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']) ? [] : explode(';', $_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS']);
        $view = $this->factory();
        return empty($requestComponentIDs) ? strval($view) : $this->refreshComponentsViewFactory($requestComponentIDs);
    }

    protected function refreshComponentsViewFactory($componentIDs)
    {
        $response = new Tag('div', 'response');
        foreach($componentIDs as $componentId) {
            $response->add(DOM::getById($componentId));
        }
        return strval($response);
    }
}
