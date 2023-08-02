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
use Osynapsy\View\Template\Template;
use Osynapsy\Html\DOM;
use Osynapsy\Html\Tag;

abstract class AbstractView implements ViewInterface
{
    protected $title;
    protected $model;
    protected $template;
    protected $meta = [];

    public function __construct(...$args)
    {
        $this->__processArgs($args);
    }

    public function __invoke(...$args)
    {
        $this->__processArgs($args);
        return $this;
    }

    protected function __processArgs(array $args)
    {
        foreach($args as $arg) {
            if (is_array($arg)) {
                $this->setProperties($arg);
            } elseif (is_object($arg) && in_array(ModelInterface::class, class_implements($arg))) {
                $this->setModel($arg);
            }
        }
    }

    public function __toString()
    {
        return strval(new ViewBuilder($this));
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

    public function getTemplate()
    {
        return $this->template;
    }

    public function getTitle() : string
    {
        return $this->title ?? '';
    }

    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        $this->model->loadValues();
        $this->setProperties(get_object_vars($this->model));
    }

    public function setProperties(array $properties = [])
    {
        foreach ($properties as $id => $value) {
            if (is_string($id)) {
                $this->{$id} = $value;
            }
        }
        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}
