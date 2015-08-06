<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Serializer;

use KoolKode\Context\ExposedContainerInterface;
use KoolKode\Context\Scope\ScopedProxyInterface;

/**
 * Serializes data using PHPs serialization format, preserving injected dependencies
 * using revivers.
 *
 * @author Martin Schröder
 */
class Serializer implements SerializerInterface
{
    private static $stack;
    private static $refCache = [];
    protected $bindClosure;
    protected $container;
    protected $revivers = [];

    public function __construct(ExposedContainerInterface $container, $bindClosure = NULL)
    {
        if (self::$stack === NULL) {
            self::$stack = new \SplStack();
        }

        $this->bindClosure = ($bindClosure === NULL) ? method_exists('Closure', 'bind') : ($bindClosure ? true : false);
        $this->container = $container;
    }

    public static function registerReviver(Reviver $reviver)
    {
        if (self::$stack->isEmpty()) {
            throw new UnserializationException('Unserialization of an object graph with dependencies requires a context-aware serializer');
        }

        self::$stack->top()->revivers[] = $reviver;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return boolean
     */
    public function isBindClosure()
    {
        return $this->bindClosure;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param boolean $bindClosure
     */
    public function setBindClosure($bindClosure)
    {
        $this->bindClosure = $bindClosure ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data)
    {
        $counter = 1;

        try {
            return $this->serializeData($data, $counter, new \SplObjectStorage());
        } catch (\Exception $e) {
            throw new SerializationException('Unable to serialize the given data', 0, $e);
        }
    }

    protected function serializeData($data, & $counter, \SplObjectStorage $cache)
    {
        if (is_object($data)) {
            return $this->serializeObject($data, $counter, $cache);
        }

        if (is_array($data)) {
            return $this->serializeArray($data, $counter, $cache);
        }

        $counter++;

        return serialize($data);
    }

    protected function serializeObject($object, & $counter, \SplObjectStorage $cache)
    {
        if ($object instanceof \Closure) {
            throw new SerializationException('Closures must not be serialized');
        }

        // Recursion checking:
        if ($cache->contains($object)) {
            $counter++;

            return 'r:' . $cache[$object] . ';';
        }

        $cache->attach($object, $counter);
        $typeName = get_class($object);

        $counter++;

        if ($object instanceof \Serializable) {
            return serialize($object);
        }

        if (method_exists($object, '__sleep')) {
            $props = [];
            $tmp = (array)$object;

            foreach ((array)$object->__sleep() as $prop) {
                if (array_key_exists($prop, $tmp)) {
                    $props[$prop] = $tmp[$prop];

                    continue;
                }

                $key = chr(0) . '*' . chr(0) . $prop;

                if (array_key_exists($key, $tmp)) {
                    $props[$key] = $tmp[$key];

                    continue;
                }

                $key = chr(0) . get_class($object) . chr(0) . $prop;

                if (array_key_exists($key, $tmp)) {
                    $props[$key] = $tmp[$key];

                    continue;
                }

                throw new SerializationException(sprintf('__sleep() returned "%s" but this property is not available on %s', $prop, get_class($object)));
            }
        } else {
            $props = (array)$object;
        }

        $serialized = 'O:' . strlen($typeName) . ':"' . $typeName;
        $serialized .= '":' . count($props) . ':{';

        foreach ($props as $k => $v) {
            $serialized .= serialize($k);

            if (is_object($v)) {
                $scope = NULL;
                $typeName = get_class($v);

                $parts = explode(chr(0), trim($k, chr(0)));

                if (count($parts) == 2 && $parts[0] !== '*') {
                    $scope = $parts[0];
                }

                $propName = array_pop($parts);

                if ($v instanceof ScopedProxyInterface) {
                    // Lazy-loaded dependency found:
                    $dependency = $v->K2GetProxyBinding()->getTypeName();
                    $serialized .= $this->serializeObject(new Reviver($object, $propName, $dependency, $scope), $counter, $cache);

                    continue;
                }

                if (NULL !== ($boundType = $this->container->getBoundTypeOfProxy($v))) {
                    $serialized .= $this->serializeObject(new Reviver($object, $propName, $boundType, $scope), $counter, $cache);

                    continue;
                }
            }

            $serialized .= $this->serializeData($v, $counter, $cache);
        }

        return $serialized . '}';
    }

    protected function serializeArray(array $data, & $counter, \SplObjectStorage $cache)
    {
        $serialized = 'a:' . count($data) . ':{';

        $counter++;

        foreach ($data as $k => $v) {
            $serialized .= serialize($k) . $this->serializeData($v, $counter, $cache);
        }

        return $serialized . '}';
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        self::$stack->push($this);

        $this->revivers = [];

        try {
            $result = unserialize($serialized);

            foreach ($this->revivers as $reviver) {
                $typeName = get_class($reviver->a);

                if (isset(self::$refCache[$typeName])) {
                    $ref = self::$refCache[$typeName];
                } else {
                    $ref = self::$refCache[$typeName] = new \ReflectionClass($typeName);
                }

                if ($this->bindClosure && !$ref->isInternal()) {
                    $callback = \Closure::bind(function ($prop, $bound) {
                        $this->$prop = $bound;
                    }, $reviver->a, ($reviver->d === NULL) ? $typeName : $reviver->d);

                    $callback($reviver->b, $this->container->get($reviver->c));
                } else {
                    if (array_key_exists($reviver->b, @$ref->getDefaultProperties())) {
                        $field = $ref->getProperty($reviver->b);
                        $field->setAccessible(true);
                        $field->setValue($reviver->a, $this->container->get($reviver->c));
                    } else {
                        $reviver->a->{$reviver->b} = $this->container->get($reviver->c);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new UnserializationException('Unable to unserialize object graph', 0, $e);
        } finally {
            $this->revivers = [];

            self::$stack->pop();
        }

        return $result;
    }
}
