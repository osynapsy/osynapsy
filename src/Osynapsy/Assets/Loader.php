<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Assets;

use Osynapsy\Mvc\Controller;

class Loader extends Controller
{
    protected $path;
    protected $basePath;

    public function init()
    {
        $this->path = $this->getParameter(0);        
        $this->basePath = __DIR__ . '/../../../assets/';
    }
    
    public function indexAction()
    {            
        if (!$this->checkFile($this->basePath . $this->path)) {
            return $this->pageNotFound();    
        }        
    }   
            
    private function checkFile($filename)
    {
        if (!is_file($filename)) {
            return false;
        }
        $this->copyFileToCache($this->request->get('page.url'), $filename);        
        $this->sendFile($filename);
        return true;
    }
    
    private function copyFileToCache($webPath, $assetsPath)
    {
        if (file_exists($webPath)) {
            return true;
        }
        $path = explode('/', $webPath);
        $file = array_pop($path);
        $currentPath = './';
        $isFirst = true;
        foreach($path as $dir){
            if (empty($dir)) {
                continue;
            }
            if (!is_writeable($currentPath)) {
                return false;
            }
            $currentPath .= $dir.'/';            
            //If first directory (__assets) not exists or isn't writable abort copy
            if ($isFirst === true && !is_writable($currentPath)) {                
                return false;
            }
            $isFirst = false;
            if (file_exists($currentPath)) {
                continue;
            }
            
            mkdir($currentPath);
        }
        $currentPath .= $file;
        if (!is_writable($currentPath)) {
            return false;
        }
        return copy($assetsPath, $currentPath);
    }
    
    public function pageNotFound()
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return 'Page not found';        
    }
    
    private function sendFile($filename)
    {
        $offset = 86400 * 7;
        // calc the string in GMT not localtime and add the offset
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        //output the HTTP header
        $this->response->setHeader('Expires', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");        
        $this->response->setContentType('text/'.$ext);
        readfile($filename);
    }
}
