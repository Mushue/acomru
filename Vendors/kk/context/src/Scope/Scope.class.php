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

use KoolKode\Context\Bind\Marker;

/**
 * Specialized markers that are being used in object scoping.
 *
 * @author Martin Schröder
 */
abstract class Scope extends Marker
{
    /**
     * Scopes must declare a no-arg public constructor.
     */
    public final function __construct()
    {
    }

    /**
     * Unwraps any scoped proxy (if present) and returns the contextual instance.
     *
     * @param object $object The object to be unwrapped.
     * @return object Contextual instance bound to the scoped proxy.
     */
    public static function unwrap($object)
    {
        if ($object instanceof ScopedProxyInterface) {
            return $object->K2UnwrapScopedProxy();
        }

        return $object;
    }
}
