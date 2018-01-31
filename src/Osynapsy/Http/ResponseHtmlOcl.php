<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

class ResponseHtmlOcl extends ResponseHtml
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
            return;
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
