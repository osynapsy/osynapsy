<?php
namespace Osynapsy\Mvc\Application;

/**
 * Description of AutoWiring
 *
 * @author peter
 */
class AutoWiring
{
    const SCALAR_TYPE = [
        'string' => '',
        'int' => 0,
        'bool' => true,
        'float' => 0.0,
        'array' => [],
        'object|string' => null
    ];

    protected $handles = [];

    public function execute($object, $method)
    {
        $dependences = $this->getMethodDependences($object, $method);
        return (new \ReflectionMethod($object, $method))->invokeArgs($object, $dependences);
    }

    protected function getMethodDependences($object, string $method)
    {
        $ref = new \ReflectionMethod($object, $method);
        $dependences = [];
        foreach ($ref->getParameters() as $parameter) {
            $parameterType = str_replace('?', '', (string) $parameter->getType());
            if (empty($parameterType) || in_array($parameterType, array_keys(self::SCALAR_TYPE))){
                $dependences[] = $parameter->getDefaultValue();
                continue;
            }
            if (array_key_exists($parameterType, $this->handles)) {
                $dependences[] = $this->handles[$parameterType];
                continue;
            }
            $dependences[] = $this->autoWiringClass($parameterType);
        }
        return $dependences;
    }

    protected function autoWiringClass($className)
    {
        $ref = new \ReflectionClass($className);
        $dependences = $this->getMethodDependences($className, '__construct');
        return empty($dependences) ? $ref->newInstance() : $ref->newInstanceArgs($dependences);
    }

    public function addHandle($class, $istance)
    {
        $this->handles[$class] = $istance;
        $interfaces = class_implements($istance) ?: [];
        foreach($interfaces as $interface) {
            $this->handles[$interface] = $istance;
        }
    }
}
