<?php

/*
 * This file is part of KoolKode Config.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Config;

/**
 * Is thrown when an attempt is made to merge incompatible configuration data. This
 * usually indicates an attempted merge of a list and a map.
 *
 * @author Martin Schröder
 */
class ConfigurationMergeException extends \RuntimeException
{
}

