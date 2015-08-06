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
 * Is thrown when a scoped proxy could not be generated or a dependency could
 * not be satisfied.
 *
 * @author Martin Schröder
 */
class ScopedProxyException extends \RuntimeException
{
}
