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
 * Manages singleton-like types with lazy-loading and scoped proxies to be able to defer
 * instantiation of objects until a method is called on them or a property is accessed.
 *
 * @author Martin Schröder
 */
class ApplicationScopeManager extends AbstractScopeManager
{
    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return ApplicationScoped::class;
    }

    /**
     * {@inheritdoc}
     */
    public function correlate(ScopedContainerInterface $container)
    {
        parent::correlate($container);

        $this->bindContext($this);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        parent::clear();

        $this->bindContext($this);
    }
}
