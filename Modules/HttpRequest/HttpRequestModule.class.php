<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 07.08.15
 * Time: 18:06
 */

namespace Modules\HttpRequest;

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\ApplicationScoped;
use KoolKode\Context\Scope\Singleton;

class HttpRequestModule extends AbstractContainerModule
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(\HttpRequestInterface::class)
            ->scoped(new Singleton())
            ->to(\HttpRequest::class);

        $builder->bind(\ServiceLocator::class)
            ->scoped(new Singleton());
    }

}