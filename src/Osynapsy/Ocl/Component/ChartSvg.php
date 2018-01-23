<?php
namespace Osynapsy\Ocl\Component;

use SVGGraph;
use Osynapsy\Data\Dictionary;

/**
 * Description of SVGGraph
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ChartSvg
{
    protected $request;
    protected $db;
    protected $repo;
    //put your code here
    public function __construct($width = 320, $height = 240, $type = 'bar', $data = array())
    {        
        $this->repo = new Dictionary(array(
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'data' => $data
        ));
    }
    
    public function render()
    {
        ob_clean();
        $svg = new SVGGraph(
            $this->get('width'),
            $this->get('height')
        );
        $svg->values(
            $this->get('data')
        );
        $svg->render(
            $this->get('type')
        );
        exit;
    }
    
    public function getHtml()
    {
        $svg = new SVGGraph(
            $this->get('width'),
            $this->get('height')
        );
        $svg->Values(
            $this->get('data')
        );
        //return;        
        return $svg->Fetch(
            $this->get('type')
        );
    }
    
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'get':
                $this->repo->get('repo'.$arguments[0]);
                break;
            default:
                $this->repo->set($name, $arguments);
                break;
        }
    }
}
