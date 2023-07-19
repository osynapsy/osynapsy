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

    public function build(ViewInterface $handle)
    {
        $requestComponentIDs = $this->getListOfComponentsToRefresh();
        $view = $handle->factory();
        if (!empty($requestComponentIDs)) {
            return $this->refreshComponentsViewFactory($requestComponentIDs);
        }
        $template = $this->templateFactory();
        $template->add(strval($view));
        return $template->get();
    }

    public function templateFactory()
    {
        $template = route()->getTemplate();
        $templateClass = empty($template['@value']) ? Template::class : $template['@value'];
        $templateHandle = new $templateClass;
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
        $response = new Tag('div', 'response');
        foreach($componentIDs as $componentId) {
            $response->add(DOM::getById($componentId));
        }
        return strval($response);
    }
}
