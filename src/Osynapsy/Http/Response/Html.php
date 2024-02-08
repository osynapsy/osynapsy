<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;

class Html extends AbstractResponse
{
    public function __construct()
    {
        parent::__construct('text/html');
        $this->streams = ['main' => []];
    }

       /**
     * Include a php page e return body string
     *
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public static function getBuffer($path)
    {
        if (!empty($path)) {
            throw new \Exception('Path parameter is empty');
        }
        if (!is_file($path)) {
            throw new \Exception('File '.$path.' not exists');
        }
        include $path;
        $buffer = ob_get_contents();
        ob_clean();
        return $buffer;
    }

    /**
     * Method that add body to the response
     *
     * @param mixed $body
     * @param mixed $partId
     * @param bool $checkUnique
     * @return mixed
     */
    public function writeStream($content, $id = 'main')
    {
        if (!array_key_exists($id, $this->streams)) {
            $this->streams[$id] = [];
        }
        $this->streams[$id][] = $content;
    }

    public function __toString()
    {
        $this->sendHeader();
        $response = '';
        foreach ($this->streams as $content) {
            $response .= is_array($content) ? implode('', $content) : $content;
        }
        return $response;
    }
}
