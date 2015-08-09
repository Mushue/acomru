<?php

namespace Modules\Core;

use KoolKode\Context\Bind\ContainerBuilder;

class CoreModule extends \KoolKode\Context\Bind\AbstractContainerModule
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        /**
         * Core
         */
        $builder->bind(\WebKernel::class)
            ->scoped(new \KoolKode\Context\Scope\Singleton())
            ->decorate(function (\WebKernel $kernel) {
                return \WebKernel::create();
            });

        /**
         * Парти вьюер
         */
        $builder->bind(\PartViewer::class);

        /**
         * Навигатион бар
         */
        $builder->bind(\NavigationBar::class)
            ->scoped(new \KoolKode\Context\Scope\Singleton());

        /**
         * Сервислокатор
         */
        $builder->bind(\ServiceLocator::class)
            ->scoped(new \KoolKode\Context\Scope\Singleton())
            ->decorate(function (\ServiceLocator $locator) {
                return \ServiceLocator::create();
            });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        /** @var \NavigationBar $navigation */
        $navigation = \Core::get(\NavigationBar::class);
        $elementsLeft = [
            //new \NavigationBarElement('Главная', '/'),
        ];
        foreach ($elementsLeft as $element) {
            $navigation->set($element, new \PositionBarLeft());
        }
    }

}