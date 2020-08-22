<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;

/**
 * Abstract Response
 */
abstract class Base
{
    protected $repo = [
        'content' => [],
        'header' => []
    ];

    /**
     * Init response with the content type
     *
     * @param type $contentType
     */
    public function __construct($contentType = 'text/html')
    {
        $this->setContentType($contentType);
    }

    public function addBufferToContent($path = null, $part = 'main')
    {
        $this->addContent($this->getBuffer($path) , $part);
    }

    /**
     * Method that add content to the response
     *
     * @param mixed $content
     * @param mixed $part
     * @param bool $checkUnique
     * @return mixed
     */
    public function addContent($content, $part = 'main', $checkUnique = false)
    {
        if ($checkUnique && !empty($this->repo['content'][$part]) && in_array($content, $this->repo['content'][$part])) {
            return;
        }
        if (!array_key_exists($part, $this->repo['content'])) {
            $this->repo['content'][$part] = [];
        }
        $this->repo['content'][$part][] = $content;
    }

    public function addValue($key, $value)
    {
        $this->repo['content'][$key] = $value;
    }

    public function clearCache()
    {
        $this->setHeader("Expires","Tue, 01 Jan 2000 00:00:00 GMT");
        $this->setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
        $this->setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
        $this->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
        $this->setHeader("Pragma","no-cache");
    }

    public function send($content, $part =  'main', $checkUnique = false)
    {
        $this->addContent($content, $part, $checkUnique);
    }

    public function exec()
    {
        $this->sendHeader();
        echo implode('',$this->repo['content']);
    }

    /**
     * Include a php page e return content string
     *
     * @param string $path
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public static function getBuffer($path = null, $controller = null)
    {
        $buffer = 1;
        if (!empty($path)) {
            if (!is_file($path)) {
                throw new \Exception('File '.$path.' not exists');
            }
            $buffer = include $path;
        }
        if ($buffer === 1) {
            $buffer = ob_get_contents();
            ob_clean();
        }
        return $buffer;
    }

    /**
     * Send header location to browser
     *
     * @param string $url
     */
    public function go($url)
    {
        header('Location: '.$url);
    }

    /**
     * Reset content part.
     *
     * @param mixed $part
     */
    public function resetContent($part = 'main')
    {
        $this->repo['content'][$part] = [];
    }

    /**
     * Set content of the response
     *
     * @param string $value
     */
    public function setContent($value)
    {
        $this->repo['content'] = $value;
    }

    /**
     * Set content type of the response
     *
     * @param string $type
     */
    public function setContentType($type)
    {
        $this->repo['header']['Content-Type'] = $type;
    }

    /**
     * Buffering of header
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->repo['header'][$key] = $value;
    }

    /**
     * Set cookie
     *
     * @param string $valueId
     * @param string $value
     * @param unixdatetime $expiry
     */
    public static function cookie($valueId, $value, $expiry = null, $excludeThirdLevel = false)
    {
        if (headers_sent()) {
           return false;
        }
        $domain = $excludeThirdLevel ? self::getDomain() : self::getServerName();
        if (empty($expiry)) {
            $expiry = time() + (86400 * 365);
        }
        return setcookie($valueId, $value, $expiry, "/", $domain);
    }

    private static function getDomain()
    {
        $domainPart = explode('.', self::getServerName());
        if (count($domainPart) > 2){
           unset($domainPart[0]);
        }
        return '.'.implode('.', $domainPart);
    }

    private static function getServerName()
    {
        return filter_input(\INPUT_SERVER, 'SERVER_NAME');
    }

    /**
     * Send header buffer
     */
    protected function sendHeader()
    {
        if (headers_sent()) {
            return;
        }
        foreach ($this->repo['header'] as $key => $value) {
            header($key.': '.$value);
        }
    }

    /**
     * Method for build response string
     * @abstract
     */
    abstract public function __toString();
}
