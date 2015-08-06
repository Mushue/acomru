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
 * Application scoped objects are shared across the application they are lazy-loaded using a proxy.
 *
 * @author Martin Schröder
 */
final class ApplicationScoped extends Scope
{
}
