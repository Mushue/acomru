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
 * Is thrown when a scoped proxy activates but the related scope is not active.
 *
 * @author Martin Schröder
 */
class ScopeNotActiveException extends \RuntimeException
{
}
