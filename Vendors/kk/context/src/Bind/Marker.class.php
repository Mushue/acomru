<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Bind;

/**
 * Markers provide a way to attach additional information to a binding that can be queried
 * and used at runtime by the DI container.
 *
 * @author Martin Schröder
 */
abstract class Marker
{
    /**
     * Check if the given object is an instance of the marker.
     *
     * @param object $object
     * @return boolean
     */
    public function isInstance($object)
    {
        return $object instanceof static;
    }
}
