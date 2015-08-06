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
 * Holds a UTF-8 encoded Unicode string in normalization form C (as of now normalization requires the
 * Normalizer class from the intl-extension).
 *
 * @author Martin Schröder
 */
class UnicodeString implements \JsonSerializable
{
    const PATTERN_UTF8 = '/(?:[\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}|.)/suS';

    protected $string = '';

    public function __construct($string = '', $inputEncoding = 'ISO-8859-1')
    {
        if ($string instanceof self) {
            $this->string = (string)$string->string;
        } else {
            $str = (string)$string;

            if ($str != '') {
// 				if(!preg_match(self::PATTERN_UTF8, $str))
                if (!preg_match('//u', $str)) {
                    $str = mb_convert_encoding($str, 'UTF-8', $inputEncoding);
                }

                if (class_exists('Normalizer', false) && !\Normalizer::isNormalized($str)) {
                    $str = \Normalizer::normalize($str);
                }

                $this->string = (string)$str;
            }
        }
    }

    public static function isUtf8($input)
    {
        if ($input instanceof self) {
            return true;
        }

        return preg_match('//u', $input) ? true : false;
// 		return preg_match(self::PATTERN_UTF8, (string)$input) ? true : false;
    }

    /**
     * Get UTF-8 character for the given codepoint
     *
     * @param integer $codepoint
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getCharacter($codepoint)
    {
        $codepoint = (int)$codepoint;

        if ($codepoint <= 0x7F) {
            return chr($codepoint);
        }

        if ($codepoint <= 0x7FF) {
            return chr(0xC0 | $codepoint >> 6) . chr(0x80 | $codepoint & 0x3F);
        }

        if ($codepoint <= 0xFFFF) {
            return chr(0xE0 | $codepoint >> 12) . chr(0x80 | $codepoint >> 6 & 0x3F) . chr(0x80 | $codepoint & 0x3F);
        }

        if ($codepoint <= 0x10FFFF) {
            return chr(0xF0 | $codepoint >> 18) . chr(0x80 | $codepoint >> 12 & 0x3F) . chr(0x80 | $codepoint >> 6 & 0x3F) . chr(0x80 | $codepoint & 0x3F);
        }

        throw new \InvalidArgumentException('Unable to create character for unicode codepoint ' . $codepoint);
    }

    /**
     * Get codepoint of a UTF-8 character
     *
     * @param string $char
     * @return integer
     *
     * @throws \InvalidArgumentException
     */
    public static function getCodepoint($char)
    {
        $char = (string)$char;

        $h = ord($char[0]);

        if ($h <= 0x7F) {
            return $h;
        }

        if ($h < 0xC2) {
            throw new \InvalidArgumentException('Could not get unicode codepoint for character: ' . $char);
        }

        if ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (ord($char[1]) & 0x3F);
        }

        if ($h <= 0xEF) {
            return (($h & 0x0F) << 12 | (ord($char[1]) & 0x3F) << 6 | (ord($char[2]) & 0x3F));
        }

        if ($h <= 0xF4) {
            return (($h & 0x0F) << 18 | (ord($char[1]) & 0x3F) << 12 | (ord($char[2]) & 0x3F) << 6 | (ord($char[3]) & 0x3F));
        }


        throw new \InvalidArgumentException('Could not get unicode codepoint for character: ' . $char);
    }

    public function __toString()
    {
        return $this->string;
    }

    public function jsonSerialize()
    {
        return $this->string;
    }

    public function append($string, $encoding = 'UTF-8')
    {
        return self::create($this->string . new self($string, $encoding));
    }

    protected function create($string)
    {
        $obj = new static();
        $obj->string = (string)$string;

        return $obj;
    }

    public function length()
    {
        return mb_strlen($this->string, 'UTF-8');
    }

    public function lengthBytes()
    {
        return strlen($this->string);
    }

    public function getChar($position)
    {
        return mb_substr($this->string, $position, 1, 'UTF-8');
    }

    public function getChars()
    {
        return preg_split("'(.)'us", $this->string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    public function contains($string)
    {
        return (false !== mb_strpos($this->string, $string, NULL, 'UTF-8'));
    }

    public function substring($start, $length = NULL)
    {
        return $this->create(mb_substr($this->string, $start, $length, 'UTF-8'));
    }

    public function substringPosition($substring, $offset = NULL)
    {
        return mb_strpos($this->string, $substring, $offset, 'UTF-8');
    }

    public function explode($delimiter, $limit = NULL)
    {
        $result = [];

        foreach (explode($delimiter, $this->string, $limit) as $part) {
            $result[] = $this->create($part);
        }

        return $result;
    }

    public function split($pattern, $limit = -1)
    {
        $result = [];

        foreach (preg_split('~' . str_replace('~', '\~', $pattern) . '~u', $this->string, $limit) as $part) {
            $result[] = $this->create($part);
        }

        return $result;
    }

    public function splitLast($pattern)
    {
        $result = preg_split('~' . str_replace('~', '\~', $pattern) . '~u', $this->string);

        if (empty($result)) {
            return NULL;
        }

        return $result[count($result) - 1];
    }

    public function trim()
    {
        return $this->create(preg_replace(["'^\s+'u", "'\s+$'u"], '', $this->string));
    }

    public function trimLeft()
    {
        return $this->create(preg_replace("'^\s+'u", '', $this->string));
    }

    public function trimRight()
    {
        return $this->create(preg_replace("'\s+$'u", '', $this->string));
    }

    public function escapeXml($escapeSingleQuotes = true)
    {
        if ($escapeSingleQuotes) {
            return $this->create(htmlspecialchars($this->string, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'));
        }

        return $this->create(htmlspecialchars($this->string, ENT_COMPAT | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'));
    }

    public function toLowerCase()
    {
        return $this->create(mb_strtolower($this->string, 'UTF-8'));
    }

    public function toUpperCase()
    {
        return $this->create(mb_strtoupper($this->string, 'UTF-8'));
    }

    protected function copy()
    {
        $obj = new static();
        $obj->string = $this->string;

        return $obj;
    }
}
