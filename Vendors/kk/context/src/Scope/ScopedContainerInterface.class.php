<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Scope;

use KoolKode\Context\ContainerInterface;

/**
 * Contract for containers that support scoping of bound types.
 *
 * @author Martin Schröder
 */
interface ScopedContainerInterface extends ContainerInterface
{
    /**
     * Get the object instance handling the given scope in this container.
     *
     * @param integer $scope The binary name of the requested scope.
     * @return ScopeManagerInterface The scope being requested.
     *
     * @throws ScopeNotFoundException When the requested scope is not registered with the container.
     */
    public function getScope($scope);

    /**
     * Load or generate a scoped proxy for the target type if it has not been loaded yet.
     *
     * @param string $typeName The name of the type to be proxied.
     * @return string The fully-qualified name of the generated class.
     *
     * @throws ScopedProxyException When an error occurs during loading / creation of the proxy.
     */
    public function loadScopedProxy($typeName);
}
