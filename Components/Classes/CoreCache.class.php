<?php


class CoreCache
{
    protected $file;
    protected $lockFile;
    protected $contextName;
    protected $timeout;
    protected $needsCheck;
    protected $cached;
    protected $dump = [];
    protected $modified = false;
    protected $instrumentationModified = false;
    protected $core;
    protected $manifest;

    public function __construct(CoreInterface $core, $timeout = 8)
    {
        $this->contextName = $core->getContextName();
        $this->core = $core;
        $this->timeout = (int)$timeout;

        $dir = $core->getCacheDirectory() . DIRECTORY_SEPARATOR . $this->contextName;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new FileNotFoundException('Cannot create dir: ' . $dir);
            }
        }
        $this->file = $dir . DIRECTORY_SEPARATOR . 'cache.php';
        $this->lockFile = $dir . DIRECTORY_SEPARATOR . 'cache.lock';

        if (is_file($this->file)) {
            if (filemtime($this->file) < (time() - $this->timeout)) {
                $this->needsCheck = true;
            }

            $this->cached = (array)require $this->file;
        } else {
            $this->modified = true;
            $this->cached = [];
        }

        $this->expired = !is_file($this->file) || filemtime($this->file) < (time() - $this->timeout);
    }

    public function isModified()
    {
        return $this->modified;
    }

    public function isInstrumentationModified()
    {
        return $this->isInstrumentationModified();
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function dumpFile()
    {
        if ($this->modified || $this->instrumentationModified) {
            file_put_contents($this->file, '<?php return ' . var_export($this->dump, true) . ';');
        } elseif (is_file($this->file)) {
            file_put_contents($this->file, '');
        }
    }

    public function loadConfiguration()
    {
        if (!$this->needsCheck && array_key_exists('config', $this->cached)) {
            $this->dump['config'] = $this->cached['config'];

            return new \KoolKode\Config\Configuration($this->cached['config']);
        }

        $loader = new \KoolKode\Config\ConfigurationLoader();
        $loader->registerLoader(new \KoolKode\Config\PhpConfigurationLoader());
        $sources = new \SplPriorityQueue();
        $params = $this->core->getContainerParams();

        foreach ($this->loadConfigurationSources() as $source) {
            $sources->insert($source, $source->getPriority());
        }

        $hash = hash_init('md5');

        foreach (clone $sources as $source) {
            hash_update($hash, $source->getKey() . ':' . $source->getLastModified() . '|');
        }

        $configHash = $this->dump['config.hash'] = hash_final($hash);

        if (isset($this->cached['config.hash']) && $this->cached['config.hash'] === $configHash) {
            $this->dump['config'] = $this->cached['config'];

            return new \KoolKode\Config\Configuration($this->cached['config']);
        }

        $this->modified = true;

        $config = new \KoolKode\Config\Configuration();

        foreach ($sources as $source) {
            $config = $config->mergeWith($source->loadConfiguration($loader, $params));
        }

        $this->dump['config'] = $config->toArray();

        return $config;
    }

    protected function loadConfigurationSources()
    {
        $sources = [];

        // Global configuration:
        $dir = PATH_CONFIGURATIONS;

        if (is_dir($dir)) {
            foreach (new \DirectoryIterator($dir) as $entry) {
                switch (strtolower($entry->getExtension())) {
                    case 'php':
                    case 'yml':
                        $sources[] = new \KoolKode\Config\ConfigurationSource($entry, CoreInterface::CONFIG_PRIORITY);
                }
            }
        }

        // Context-specific configuration:
        $dir = PATH_CONFIGURATIONS . $this->contextName;

        if (is_dir($dir)) {
            foreach (new \DirectoryIterator($dir) as $entry) {
                switch (strtolower($entry->getExtension())) {
                    case 'php':
                    case 'yml':
                        $sources[] = new \KoolKode\Config\ConfigurationSource($entry, CoreInterface::CONFIG_CONTEXT_PRIORITY);
                }
            }
        }

        return $sources;
    }

    public function syncContainer(CoreInterface $kernel, \KoolKode\Context\Bind\ContainerModuleLoader $loader, \KoolKode\Context\Scope\ScopeLoader $scopes)
    {
        if (!$this->needsCheck && array_key_exists('container', $this->cached)) {
            $this->dump['container'] = $this->cached['container'];

            return;
        }

        $ktime = filemtime((new \ReflectionClass(get_class($kernel)))->getFileName());
        $ctime = $loader->getLastModified();
        $chash = $loader->getHash();
        $shash = $scopes->getHash();

        if (!array_key_exists('container', $this->cached)) {
            $this->modified = true;
        } else {
            if (!array_key_exists('ktime', $this->cached['container']) || $ktime !== $this->cached['container']['ktime']) {
                $this->modified = true;
            }

            if (!array_key_exists('ctime', $this->cached['container']) || $ctime !== $this->cached['container']['ctime']) {
                $this->modified = true;
            }

            if (!array_key_exists('chash', $this->cached['container']) || $chash !== $this->cached['container']['chash']) {
                $this->modified = true;
            }

            if (!array_key_exists('shash', $this->cached['container']) || $shash !== $this->cached['container']['shash']) {
                $this->modified = true;
            }
        }

        $this->dump['container'] = [
            'ktime' => $ktime,
            'ctime' => $ctime,
            'chash' => $chash,
            'shash' => $shash
        ];
    }

    public function syncSources()
    {

    }

    public function syncInstrumentation()
    {

    }

    protected function collectLoaders()
    {
        $loaders = [];

        return $loaders;
    }

    protected function collectSources($dir, callable $callback)
    {
        foreach (glob($dir . '/*') as $entry) {
            if (is_dir($entry)) {
                $this->collectSources($entry, $callback);
            } elseif (is_file($entry) && 'php' === strtolower(pathinfo($entry, PATHINFO_EXTENSION))) {
                $callback($entry);
            }
        }
    }
}