<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http;

use Osynapsy\DataStructure\Dictionary;
use Osynapsy\Routing\Route;

class Request extends Dictionary
{
    /**
     * Constructor.
     *
     * @param array           $get        The GET parameters
     * @param array           $post       The POST parameters
     * @param array           $request    The REQUEST attributes (parameters parsed from the PATH_INFO, ...)
     * @param array           $cookies    The COOKIE parameters
     * @param array           $files      The FILES parameters
     * @param array           $server     The SERVER parameters
     * @param string|resource $content    The raw body data
     *
     * @api
     */
    public function __construct(array $get = [], array $post = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->set('get', $get)
             ->set('post', $post)
             ->set('request', $request)
             ->set('cookies', $cookies)
             ->set('files', $files)
             ->set('server', $server)
             ->set('content', $content);
        $rawHost = (isset($server['HTTPS']) && $server['HTTPS'] == 'on') ? 'https://' : 'http://';
        $rawHost .= $this->get('server.HTTP_HOST');
        $url = $rawHost.$this->get('server.REQUEST_URI');
        $this->set('page.url', $url);
        $this->set('page.uri', $this->get('server.REQUEST_URI'));
        $this->set('server.RAW_URL_PAGE', $url);
        $this->set('server.RAW_URL_SITE', $rawHost);
        $this->set('server.url', $rawHost);
        if (!empty($server['HTTP_ACCEPT'])) {
            $this->set('client.accept', explode(',', $server['HTTP_ACCEPT']));
        }
        $this->headerFactory($server);
    }

    protected function headerFactory($server)
    {
        $header = [];
        foreach ($server as $key => $headerValue) {
            if (preg_match('/^HTTP_/', $key)) {
                $httpHeaderKey = strtr(ucwords(strtolower(strtr(substr($key,5), '_', ' '))),' ','-');
                $header[$httpHeaderKey] = $headerValue;
            }
        }
        $this->set('header', $header);
    }

    public function hasHeader($headerId)
    {
        return $this->keyExists('header.'.$headerId);
    }

    public function getAcceptedContentType()
    {
        return $this->get('client.accept');
    }

    public function getRoute($routeId = null)
    {
        return is_null($routeId) ? $this->get('route') : Route::createFromArray($this->findRuote($routeId));
    }

    protected function findRuote($routeId)
    {
        $routes = $this->search('route', 'env.app');
        $result = array_search($routeId, array_column($routes, 'id'));
        if ($result !== false) {
            return $routes[$result];
        }
        throw new \Exception(sprintf('Route %s not found', $routeId));
    }

    public function getTemplate($id)
    {
        return empty($id) ? [] : $this->get(sprintf('app.templates.%s', $id));
    }

    public function __invoke($key)
    {
        return $this->get($key);
    }
}
