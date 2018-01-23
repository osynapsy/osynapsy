<?php
namespace Osynapsy\Http\Request;

use Osynapsy\Data\Dictionary;

class Request extends Dictionary
{
    /**
     * Constructor.
     *
     * @param array           $query      The GET parameters
     * @param array           $request    The POST parameters
     * @param array           $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array           $cookies    The COOKIE parameters
     * @param array           $files      The FILES parameters
     * @param array           $server     The SERVER parameters
     * @param string|resource $content    The raw body data
     *
     * @api
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->set('query', $query)
             ->set('request', $request)
             ->set('attributes', $attributes)
             ->set('cookies', $cookies)
             ->set('files', $files)
             ->set('server', $server)
             ->set('content', $content);
        $rawHost = (isset($server['HTTPS']) && $server['HTTPS'] == 'on') ? 'https://' : 'http://';
        $rawHost .= $this->get('server.HTTP_HOST');
        $url = $rawHost.$this->get('server.REQUEST_URI');
        $this->set('page.url',$url);
        $this->set('server.RAW_URL_PAGE',$url);
        $this->set('server.RAW_URL_SITE',$rawHost);
    }
}
