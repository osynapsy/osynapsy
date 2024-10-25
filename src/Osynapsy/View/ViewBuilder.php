<?php
namespace Osynapsy\View;

use Osynapsy\Html\DOM;
use Osynapsy\Html\Tag;
use Osynapsy\View\Template\Template;

/**
 * Description of ViewBuilder
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class ViewBuilder
{
    const HEADER_REFRESH_COMPONENT_REQUEST_KEY = 'HTTP_OSYNAPSY_HTML_COMPONENTS';

    protected $viewHandle;
    protected $view;

    public function __construct(ViewInterface $handle)
    {
        $this->viewHandle = $handle;
        $this->view = $handle->factory();
    }

    public function __toString()
    {
        $requestComponentIDs = $this->getListOfComponentsToRefresh();
        if (!empty($requestComponentIDs)) {
            return $this->refreshComponentsViewFactory($requestComponentIDs);
        }
        $template = $this->templateFactory($this->viewHandle->getTitle(), route()->getTemplate());
        $template->add(strval($this->view));
        return $template->get();
    }

    public function templateFactory($pageTitle, $template)
    {
        $templateClass = empty($template['@value']) ? Template::class : $template['@value'];
        $templateHandle = new $templateClass;
        $templateHandle->setTitle($pageTitle);
        $templateHandle->init();
        if (!empty($template) && !empty($template['path'])) {
            $templateHandle->setPath($template['path']);
        }
        return $templateHandle;
    }

    protected function getListOfComponentsToRefresh()
    {
        $listIDs = $_SERVER[self::HEADER_REFRESH_COMPONENT_REQUEST_KEY] ?? null;
        return empty($listIDs) ? [] : explode(';', $listIDs);
    }

    protected function refreshComponentsViewFactory($componentIDs)
    {
        $Dummy = new Tag('dummy');
        $Lib = $Dummy->add(new Tag('div', 'responseLibs'));
        foreach (DOM::getRequire() as $require) {
            $path = $require[2] ?? $require[0];
            switch($require[1] ?? 'css') {
                case 'js':
                    if (str_ends_with($path, '.js')) {
                        $Lib->add(new Tag('script', sha1($path)))->attributes(['src' => $path]);
                    } else {
                        $Lib->add(new Tag('script', sha1($path)))->add(['src' => $path]);
                    }
                    break;
                case 'style':
                    $Lib->add(new Tag('style', sha1($path)))->add($path);
                    break;
                case 'css':
                    $Lib->add(new Tag('link', sha1($path)))->attributes(['href' => $path, 'rel' => 'stylesheet']);
                    break;
            }
        }
        $response = $Dummy->add(new Tag('div', 'response'));
        foreach($componentIDs as $componentId) {
            $response->add(DOM::getById($componentId));
        }
        return strval($Dummy);
    }
}
