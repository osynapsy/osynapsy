<?php
namespace Osynapsy\Helper;

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

    public function __construct(array $handles = [])
    {
        array_walk($handles, function($handle) { $this->addHandle($handle); });
    }

    public function execute($object, $method, $parameters = [])
    {
        $dependences = $this->getMethodDependences($object, $method, $parameters);
        return (new \ReflectionMethod($object, $method))->invokeArgs($object, $dependences);
    }

    protected function getMethodDependences($object, string $method, array $externalParameters = [])
    {
        $ref = new \ReflectionMethod($object, $method);
        $dependences = [];
        $externalParameterIdx = 0;
        foreach ($ref->getParameters() as $parameter) {
            $parameterType = str_replace('?', '', (string) $parameter->getType());
            if (array_key_exists($parameterType, $this->handles)) {
                $dependences[] = $this->handles[$parameterType];
                continue;
            }
            if (!class_exists($parameterType ?? 'dummy') && array_key_exists($externalParameterIdx, $externalParameters)) {
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

    public function addHandle($handle, $class = null)
    {
        if (!is_object($handle)) {
            return;
        }
        $handleId = empty($class) ? get_class($handle) : $class;
        $this->handles[$handleId] = $handle;
        $interfaces = class_implements($handle) ?: [];
        foreach($interfaces as $interface) {
            $this->handles[$interface] = $handle;
        }
    }
}
