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
 * Represents an Internet media type (also known as MIME type).
 *
 * @author Martin Schröder
 */
class MediaType implements \Countable, \IteratorAggregate, \JsonSerializable
{
    protected $type;
    protected $subtypes = [];

    public function __construct($type, array $subtypes = NULL)
    {
        if ($type instanceof self) {
            $this->type = $type->type;
            $this->subtypes = $type->subtypes;
        } elseif ($subtypes !== NULL) {
            $this->type = strtolower(trim($type));
            $this->subtypes = array_map('strtolower', array_map('trim', $subtypes));
        } else {
            $parts = explode('/', $type, 2);
            $this->type = strtolower(trim($parts[0]));

            if (count($parts) == 2) {
                $this->subtypes = array_map('trim', explode('+', strtolower($parts[1])));
            }
        }

        if (strlen($this->type) < 1) {
            throw new InvalidMediaTypeException('Media type must not be empty');
        }

        foreach ($this->subtypes as $index => $text) {
            if (trim($text) == '') {
                unset($this->subtypes[$index]);
            }
        }

        if (empty($this->subtypes)) {
            throw new InvalidMediaTypeException('At least 1 subtype must be specified for type "' . $this->type . '"');
        }

        if (in_array('*', $this->subtypes) && count($this->subtypes) != 1) {
            throw new InvalidMediaTypeException('A wildcard subtype must not contain additional subtypes: "' . implode('+', $this->subtypes) . '"');
        }
    }

    public function __toString()
    {
        return $this->type . '/' . implode('+', $this->subtypes);
    }

    public function jsonSerialize()
    {
        return (string)$this;
    }

    public function count()
    {
        return count($this->subtypes);
    }

    public function getIterator()
    {
        $data = [];

        foreach ($this->subtypes as $subtype) {
            $data[] = new self($this->type, [$subtype]);
        }

        return new \ArrayIterator($data);
    }

    public function getSubTypes()
    {
        return $this->subtypes;
    }

    public function is($type)
    {
        if (!$type instanceof self) {
            $type = new self($type);
        }

        if ($this->type != '*' && $type->getType() != '*' && $this->type != $type->getType()) {
            return false;
        }

        if (in_array('*', $type->subtypes)) {
            return true;
        }

        foreach ($type->subtypes as $subtype) {
            if (in_array($subtype, $this->subtypes)) {
                return true;
            }
        }

        return false;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isType($type)
    {
        if ($type == '*' || $this->type == '*') {
            return true;
        }

        return $this->type == $type;
    }

    public function isSubType($type)
    {
        if ($type == '*') {
            return true;
        }

        foreach ($this->subtypes as $subtype) {
            if ($subtype == $type || $subtype == '*') {
                return true;
            }
        }

        return false;
    }

    public function isWildcardType()
    {
        if ($this->type == '*') {
            return true;
        }

        foreach ($this->subtypes as $subtype) {
            if ($subtype == '*') {
                return true;
            }
        }

        return false;
    }

    public function isApplication()
    {
        return $this->type == 'application';
    }

    public function isAudio()
    {
        return $this->type == 'audio';
    }

    public function isText()
    {
        return $this->type == 'text';
    }

    public function isImage()
    {
        return $this->type == 'image';
    }

    public function isVideo()
    {
        return $this->type == 'video';
    }

    public function isMultipart()
    {
        return $this->type == 'multipart';
    }
}
