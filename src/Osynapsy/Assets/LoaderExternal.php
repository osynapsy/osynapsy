<?php
namespace Osynapsy\Assets;

use Osynapsy\Kernel;

/**
 * Description of LoaderExternal
 *
 * @author pietro
 */
class LoaderExternal extends Loader
{
    protected $assetsPath;

    public function init()
    {
        $assetsPathRequest = $this->getParameter(0);
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
        strtok($rawPackagePath, '..'); //$composerPath
        $rootPathSubPackage = strtok('../');
        $packagePath = realpath(substr($rawPackagePath, 0, strpos($rawPackagePath, $rootPathSubPackage) + strlen($rootPathSubPackage)));
        $files = glob($packagePath . str_replace($namespaceKeySearch, '/*', $assetsPathRequest));
        $this->assetsPath = !empty($files) ? $files[0] : '';
    }

    protected function getPackagePathFromComposer($namespaceKeySearch)
    {
        return array_filter(
            Kernel::getComposer()->getPrefixesPsr4(),
            function($namespace) use ($namespaceKeySearch) { return sha1($namespace) === $namespaceKeySearch;},
            ARRAY_FILTER_USE_KEY
        );
    }

    public function indexAction()
    {
        return $this->getFile($this->assetsPath);
    }
}
