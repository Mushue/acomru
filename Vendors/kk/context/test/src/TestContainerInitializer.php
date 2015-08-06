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

use KoolKode\Context\Bind\ContainerInitializerInterface;

class TestContainerInitializer implements ContainerInitializerInterface
{
    public function initializeObject($object, ContainerInterface $container)
    {
        if ($object instanceof ContainerAwareMock) {
            $object->setMessage('DONE');
        }
    }
}
