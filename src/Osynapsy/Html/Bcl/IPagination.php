<?php
namespace Osynapsy\Html\Bcl;

/**
 * Description of IPagination
 *
 * @author Pietro
 */
interface IPagination
{
    public function setParentComponent(string $id);

    public function loadData($requestPage, $exceptionOnError = false);

    public function getOrderBy();

    public function getPageDimensionsCombo();

    public function getStatistic($key);

    public function getMeta($key);
}
