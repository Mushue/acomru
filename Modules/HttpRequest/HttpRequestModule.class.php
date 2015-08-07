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

class HttpRequestModule extends AbstractContainerModule
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(\HttpRequestInterface::class)
            ->to(\HttpRequest::class);
    }

}