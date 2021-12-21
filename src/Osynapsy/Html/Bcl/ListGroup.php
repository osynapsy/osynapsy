<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Bcl\Link;

/**
 * Description of ListGroup
 *
 * @author Pietro
 */
class ListGroup extends Component
{
    protected $repo = [];
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
        parent::__construct('div', $id);
        $this->setClass('list-group');
    }

    public function __build_extra__(): void
    {
        foreach ($this->repo as $item) {
            $this->add($item);
        }
    }

    public function addLink($label, $uri)
    {
        $id = count($this->repo);
        $this->repo[$id] = new Link(sprintf('%s_%s', $this->id, $id), $uri, $label, 'list-group-item list-group-item-action');
    }

    public function setFixedPosition($width = '100%', $top = '0px', $left = '0px')
    {
        $this->addClass('position-fixed d-none d-lg-block');
        $this->att('style', sprintf('width : %s; top : %s; left : %s;', $width, $top, $left));
    }
}
