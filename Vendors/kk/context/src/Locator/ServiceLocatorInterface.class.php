<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Locator;

/**
 * Contract for a service locator that can be used where access to a collection of
 * named services (= objects) is required (plugin managenent, helpers, ...).
 *
 * @author Martin Schröder
 */
interface ServiceLocatorInterface extends \Countable, \IteratorAggregate
{
    /**
     * Get the service registered under the given name.
     *
     * @param string $name
     * @return object
     *
     * @throws ServiceNotFoundException
     */
    public function getService($name);
}
