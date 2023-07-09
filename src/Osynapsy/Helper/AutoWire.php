<?php
namespace Osynapsy\Helper;

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

    protected static $handles = [];

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
            if (!class_exists($parameterType ?? '__dummy__') && array_key_exists($externalParameterIdx, $externalParameters)) {
                $dependences[] = $externalParameters[$externalParameterIdx++];
                continue;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $dependences[] = $parameter->getDefaultValue();
                continue;
            }
            if (empty($parameterType) || in_array($parameterType, array_keys(self::SCALAR_TYPE))) {
                //var_dump($object, $parameterType, $method, $parameter);
                //exit;
                continue;
            }
            $dependences[] = $this->getInstance($parameterType);
        }
        return $dependences;
    }

    public function getInstance($className)
    {
        $ref = new \ReflectionClass($className);
        $dependences = $this->getDependences($ref->getConstructor());
        return empty($dependences) ? $ref->newInstance() : $ref->newInstanceArgs($dependences);
    }

    public function addHandle($handle, $class = null)
    {
        if (!is_object($handle)) {
            return;
        }
        $handleId = empty($class) ? get_class($handle) : $class;
        self::$handles[$handleId] = $handle;
        $interfaces = class_implements($handle) ?: [];
        foreach($interfaces as $interface) {
            self::$handles[$interface] = $handle;
        }
    }
}
