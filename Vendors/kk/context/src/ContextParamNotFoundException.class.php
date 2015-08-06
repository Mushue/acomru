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
 * Is thrown when a context parameter is not set and no default value has
 * been given to the accessor.
 *
 * @author Martin Schröder
 */
class ContextParamNotFoundException extends \OutOfBoundsException
{
}
