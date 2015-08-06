<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context;

/**
 * Is thrown when an object instance could not be retrieved by the DI container, most of the time
 * this is thrown due to some exception during creation which will be available as previous exception.
 *
 * @author Martin Schröder
 */
class ContextLookupException extends \RuntimeException
{
}
