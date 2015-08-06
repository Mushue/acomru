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
 * Is thrown when a service by the same name is registered multiple times in a service locator.
 *
 * @author Martin Schröder
 */
class DuplicateServiceRegistrationException extends \RuntimeException
{
}
