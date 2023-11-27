<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Helper;

class Upload
{
    private $documentRoot;
    private $componentId;
    private $repoPath;

    public function __construct($componentId, $repoPath = '/upload')
    {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $this->componentId = $componentId;
        $this->repoPath = $repoPath;
    }

    public function save()
    {
        list($filename, $filenameTmp) = $this->validateUploadData($this->componentId, $this->repoPath);
        $pathOnDisk = $this->getUniqueFilename($this->repoPath, $filename);
        $pathOnWeb = str_replace($this->documentRoot,'',$pathOnDisk);
        if ($pathOnDisk && move_uploaded_file($filenameTmp, $pathOnDisk)){
            return $pathOnWeb;
        }
        return '';
    }

    private function validateUploadData($componentId, $repositoryPath)
    {
        $fullRepoPath = $this->documentRoot . $repositoryPath;
        if (!array_key_exists($componentId, $_FILES)) {
            throw new \Exception(sprintf('No Files object %s exists' , $componentId));
        }
        if (empty($_FILES[$componentId]['name'])) {
            throw new \Exception('Filename is empty for field '.$componentId, 404);
        }
        if (empty($_FILES[$componentId]['tmp_name'])) {
            throw new \Exception('Temporary filename is empty for field '.$componentId);
        }
        if (!is_dir($fullRepoPath)) {
            $this->createDir($fullRepoPath);
        }
        if (!is_writeable($fullRepoPath)) {
            throw new \Exception(sprintf('%s is not writeable.', $fullRepoPath));
        }
        return [$_FILES[$componentId]['name'], $_FILES[$componentId]['tmp_name']];
    }

    public function getUniqueFilename($repositoryPath, $filename)
    {
        $pathOnDisk = $this->documentRoot . rtrim($repositoryPath, '/') . '/' . $filename;
        if (!file_exists($pathOnDisk)) {
            return $pathOnDisk;
        }
        $pathInfo = pathinfo($pathOnDisk);
        $i = 1;
        while (file_exists($pathOnDisk)) {
            $pathOnDisk = sprintf('%s/%s_%s.%s', $pathInfo['dirname'], $pathInfo['filename'], $i, $pathInfo['extension']);
            $i++;
        }
        return $pathOnDisk;
    }

    private function createDir($dir)
    {
        $result = @mkdir($dir, 0775, true);
        if (empty($result)) {
            throw new \Exception(sprintf('Create %s dir is not possibile', $dir));
        }
        return $this;
    }
}
