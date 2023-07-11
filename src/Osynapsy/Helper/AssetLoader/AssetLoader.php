<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Helper\AssetLoader;

use Osynapsy\Controller\AbstractController;
use Osynapsy\Kernel\KernelException;
use Osynapsy\Routing\Route;

class AssetLoader extends AbstractController
{
    protected $assetsPath;

    public function init(Route $route)
    {
        $assetsPathRequest = $route->getParameter(0);
        $namespaceKeySearch = strtok($assetsPathRequest, '/');
        $this->setAssetPath($namespaceKeySearch, $assetsPathRequest);
    }

    protected function setAssetPath($namespaceKeySearch, $assetsPathRequest)
    {
        $resNamespaces = $this->getPackagePathFromComposer($namespaceKeySearch);
        if (empty($resNamespaces)) {
            return;
        }
        $namespaceOfPackage = array_key_first($resNamespaces);
        $rawPackagePath = $resNamespaces[$namespaceOfPackage][0];
        strtok(str_replace('../','#',$rawPackagePath), '#'); //$composerPath
        $rootPathSubPackage = strtok('#/');
        $packagePath = realpath(substr($rawPackagePath, 0, strpos($rawPackagePath, $rootPathSubPackage) + strlen($rootPathSubPackage)));
        $files = glob($packagePath . str_replace($namespaceKeySearch, '/*/assets', str_replace('../', '',$assetsPathRequest)));
        $this->assetsPath = !empty($files) ? $files[0] : '';
    }

    protected function getPackagePathFromComposer($namespaceKeySearch)
    {
        return array_filter(
            $this->getApp()->getComposer()->getPrefixesPsr4(),
            function($namespace) use ($namespaceKeySearch) { return sha1($namespace) === $namespaceKeySearch;},
            ARRAY_FILTER_USE_KEY
        );
    }

    public function index()
    {
        return $this->getFile($this->assetsPath);
    }

    protected function getFile($filename)
    {
        if (!is_file($filename)) {
            throw new KernelException('Page not found', 404);
        }
        $this->copyFileToCache($this->getRequest()->get('page.url'), $filename);
        return $this->sendFile($filename);
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

    private function sendFile($filename)
    {
        $offset = 86400 * 7;
        // calc the string in GMT not localtime and add the offset
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        //output the HTTP header
        $this->getResponse()->withHeader('Expires', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
        switch($ext) {
            case 'js':
                $this->getResponse()->setContentType('application/javascript');
                break;
            default:
                $this->getResponse()->setContentType('text/'.$ext);
                break;
        }
        return file_get_contents($filename);
    }
}
