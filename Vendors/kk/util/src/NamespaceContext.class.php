<?php

/*
 * This file is part of KoolKode Utilities.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Util;

/**
 * The runtime namespace context holds all data needed to determine qualifed names within a namespace.
 *
 * @author Martin Schröder
 */
class NamespaceContext
{
    /**
     * Namespace of the calling code position (a value of NULL indicates global namespace).
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Imported types and namespaces and their lowercase alias names.
     *
     * @var array<string, string>
     */
    protected $imports = [];

    /**
     * Create a new namespace context instance in the given namespace.
     *
     * @param string $namespace
     * @param array $imports
     */
    public function __construct($namespace = '', array $imports = [])
    {
        $this->namespace = trim($namespace, '\\');
        $this->imports = $imports;
    }

    /**
     * Compiles the namespace context into PHP code that creates a new instance of the context.
     *
     * @return string
     */
    public function compile()
    {
        $code = 'new \\' . get_class($this) . '(';
        $code .= var_export($this->namespace, true) . ', [';

        $index = 0;

        foreach ($this->imports as $k => $v) {
            if ($index++ != 0) {
                $code .= ', ';
            }

            $code .= var_export($k, true) . ' => ' . var_export($v, true);
        }

        return $code . '])';
    }

    /**
     * Check if the namespace context refers to global space.
     *
     * @return boolean
     */
    public function isGlobalNamespace()
    {
        return $this->namespace == '';
    }

    /**
     * Get the namespace name.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Check if an import for the given name is present.
     *
     * @param string $importName
     * @return boolean
     */
    public function hasImport($importName)
    {
        return array_key_exists(strtolower(trim($importName, '\\')), $this->imports);
    }

    /**
     * Add an import directive to the namespace context, the imported type is
     * addressed using the local name.
     *
     * @param string $importName
     * @return NamespaceContext
     */
    public function addImport($importName)
    {
        $importName = trim($importName, '\\');

        if (false !== ($offset = strrpos($importName, '\\'))) {
            $this->imports[strtolower(substr($importName, $offset + 1))] = $importName;
        } else {
            $this->imports[strtolower($importName)] = $importName;
        }

        return $this;
    }

    /**
     * Add an aliased import to the namespace context, the imported type or
     * namespace is addressed using the given alias name.
     *
     * @param string $aliasName The local alias name of the type or namespace.
     * @param string $importName The fully qualified name of the type or namespace.
     * @return NamespaceContext
     */
    public function addAliasedImport($aliasName, $importName)
    {
        $this->imports[strtolower(trim($aliasName))] = trim($importName, '\\');

        return $this;
    }

    /**
     * Get the fully qualified name of the given local type within the namespace context.
     *
     * @param string $localType
     * @return string
     */
    public function lookup($localType)
    {
        $localType = trim($localType);

        if ($localType === '') {
            return $this->namespace;
        }

        if ($localType[0] === '\\') {
            return trim($localType, '\\');
        }

        $type = strtolower($localType);

        if (strpos($type, '\\') === false) {
            // Type import lookup.
            if (array_key_exists($type, $this->imports)) {
                return $this->imports[$type];
            }
        } else {
            // Namespace import lookup.
            $tmp = explode('\\', $localType, 2);
            $tmp[0] = strtolower($tmp[0]);

            if (array_key_exists($tmp[0], $this->imports)) {
                return ltrim($this->imports[$tmp[0]] . '\\' . $tmp[1], '\\');
            }
        }

        return ltrim($this->namespace . '\\', '\\') . $localType;
    }
}
