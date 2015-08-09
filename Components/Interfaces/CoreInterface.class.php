<?php

interface CoreInterface
{
    const CONFIG_PRIORITY = 500;
    const CONFIG_CONTEXT_PRIORITY = 400;

    public function getContextName();

    public function getDirectory();

    public function getProjectDirectory();

    public function getCacheDirectory();

    public function getMigrationDirectory();

    public function getConfiguration();

    public function getManifest();

    public function getInstrumentors();

    public function getResource($resource);

    public function getContainerParams();

    public function loadContainerModules(\KoolKode\Context\Bind\ContainerModuleLoader $loader);

    public function run($terminate = true);
}