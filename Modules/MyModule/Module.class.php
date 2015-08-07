<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 07.08.15
 * Time: 16:00
 */

namespace Modules\MyModule;

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\ApplicationScoped;

class Module extends AbstractContainerModule
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(ModuleInterface::class)
            ->scoped(new ApplicationScoped());
    }

}