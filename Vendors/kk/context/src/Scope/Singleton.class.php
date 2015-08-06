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
 * Singletons are instantiated when they are injected for the first time (as opposed to application
 * scoped objects that are instantiated when they are accessed).
 *
 * @author Martin Schröder
 */
final class Singleton extends Scope
{
}
