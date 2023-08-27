<?php
namespace Osynapsy\Helper;

use Osynapsy\Routing\Route;
use Osynapsy\Application\Session;

/**
 * Description of AutoWiring
 *
 * @author peter
 */
class AutoWire
{
    const SCALAR_TYPE = [
        'string' => '',
        'int' => 0,
        'bool' => true,
        'float' => 0.0,
        'array' => [],
        'object|string' => null
    ];

    public static $handles = [];

    public function __construct(array $handles = [])
    {
        array_walk($handles, function($handle) { $this->addHandle($handle); });
    }

    public function execFunction($function, array $parameters = [])
    {
        $reflectionFunction = new \ReflectionFunction($function);
        $dependences = $this->getDependences($reflectionFunction, $parameters);
        return $reflectionFunction->invokeArgs($dependences);
    }

    public function execute($object, $method, array $parameters = [])
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $dependences = $this->getDependences($reflectionMethod, $parameters);
        return $reflectionMethod->invokeArgs($object, $dependences);
    }

    protected function getDependences($reflectionObject, array $externalParameters = [])
    {
        $dependences = [];
        $externalParameterIdx = 0;
        foreach ($reflectionObject->getParameters() as $parameter) {
            $parameterType = str_replace('?', '', (string) $parameter->getType());
            if (array_key_exists($parameterType, self::$handles)) {
                $dependences[] = self::$handles[$parameterType];
                continue;
            }
            if (self::hasHandle(Session::class) && self::getHandle(session::class)->keyExists($parameter->getName())) {
                $dependences[] = self::getHandle(Session::class)->get($parameter->getName());
                continue;
            }
            if (self::hasHandle(Route::class) && self::getHandle(Route::class)->hasParameter($parameter->getName())) {
                $dependences[] = self::getHandle(Route::class)->getParameter($parameter->getName());
                continue;
            }
            if (!class_exists($parameterType ?? '__dummy__') && array_key_exists($externalParameterIdx, $externalParameters)) {
                $dependences[] = $externalParameters[$externalParameterIdx++];
                continue;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $dependences[] = $parameter->getDefaultValue();
                continue;
            }
            if (empty($parameterType) || in_array($parameterType, array_keys(self::SCALAR_TYPE))) {
                continue;
            }
            $dependences[] = $this->getInstance($parameterType);
        }
        return $dependences;
    }

    public function getInstance($className)
    {
        $ref = new \ReflectionClass($className);
        $constructor = $ref->getConstructor();
        $dependences = !empty($constructor) ? $this->getDependences($constructor) : [];
        return empty($dependences) ? $ref->newInstance() : $ref->newInstanceArgs($dependences);
    }

    public static function addHandle($handle, $class = null)
    {
        if (!is_object($handle)) {
            return;
        }
        $dummies = [$class ?: get_class($handle)] + (class_implements($handle) ?: []) + (class_parents($handle) ?: []);
        foreach($dummies as $id) {
            self::$handles[$id] = $handle;
        }
        return $handle;
    }

    public static function getHandle($handleId)
    {
        return self::$handles[$handleId];
    }

    public static function hasHandle($handleId)
    {
        return array_key_exists($handleId, self::$handles);
    }
}
