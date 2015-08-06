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

/**
 * Revivers are simple value objects thar are serialized in place of injected dependencies and
 * trigger re-injection of dependencies by serializers.
 *
 * @author Martin Schröder
 */
final class Reviver
{
    /**
     * Holds a reference to the object that receives the injected dependency.
     *
     * @var object
     */
    public $a;

    /**
     * Holds the name of the field that needs to be injected.
     *
     * @var string
     */
    public $b;

    /**
     * Holds the type name of the bound dependency as needed to pull it from the container.
     *
     * @var string
     */
    public $c;

    /**
     * Holds the scope of the field (required when injecting private fields) or NULL when
     * the field is not private.
     *
     * @var string
     */
    public $d;

    /**
     * Creates a reviver that enables re-injection of dependencies on unserialization.
     *
     * @param object $object The object instance that receives the dependency.
     * @param string $property The name of the property to be re-injected.
     * @param string $dependency The bound name of the injected dependency in the container.
     * @param string $scope The scope of a private field or NULL.
     */
    public function __construct($object, $property, $dependency, $scope = NULL)
    {
        $this->a = $object;
        $this->b = $property;
        $this->c = (string)$dependency;
        $this->d = ($scope === NULL) ? NULL : (string)$scope;
    }

    /**
     * Registers the reviver with the serializer.
     *
     * @throws UnserializationException
     */
    public function __wakeup()
    {
        try {
            Serializer::registerReviver($this);
        } catch (\Exception $e) {
            // Preserving the exception trace by re-throwing or nesting crashes PHP...
            throw new UnserializationException($e->getMessage());
        }
    }
}
