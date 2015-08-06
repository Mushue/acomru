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

/**
 * Is thrown when an attempt is made to access a scope not registered with the DI container / context.
 *
 * @author Martin Schröder
 */
class ScopeNotFoundException extends \OutOfBoundsException
{
}
