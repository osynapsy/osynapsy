<?php
namespace Osynapsy\Ocl\Response;

use Osynapsy\Core\Response\HtmlResponse;
use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;

class Html extends HtmlResponse
{
    protected function buildResponse()
    {
        $componentIds = array();
        if (!empty($_REQUEST['ajax'])) {
            $componentIds = is_array($_REQUEST['ajax']) ? $_REQUEST['ajax'] : array($_REQUEST['ajax']);
        }
        if (!empty($componentIds)) {
            $this->resetTemplate();
            $this->resetContent();
            $response = new Tag('div');
            $response->att('id','response');
            foreach($componentIds as $id) {
                $response->add(Component::getById($id));                    
            }
            $this->addContent($response);       
            return $this->response;
        }
        if (!$requires = Component::getRequire()) {
            return;
        }
        foreach ($requires as $type => $urls) {
            foreach ($urls as $url){
                switch($type) {
                    case 'js':
                        $this->addJs($url);
                        break;
                    case 'jscode':
                        $this->addJsCode($url);
                        break;
                    case 'css':
                        $this->addCss($url);
                        break;
                }
            }
        }
    }
}
