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

class Html extends Base
{
    public function __construct()
    {
        parent::__construct('text/html');
        $this->body = ['main' => []];
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

    public function __toString()
    {
        $this->sendHeader();
        $response = '';
        foreach ($this->body as $content) {
            $response .= is_array($content) ? implode('',$content) : $content;
        }
        return $response;
    }
}
