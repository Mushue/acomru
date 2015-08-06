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
 * Is thrown when a service locator cannot provide the requested service.
 *
 * @author Martin Schröder
 */
class ServiceNotFoundException extends \OutOfBoundsException
{
}
