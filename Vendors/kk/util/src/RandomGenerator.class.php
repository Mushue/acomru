<?php

/*
 * This file is part of KoolKode Util.
 *
 * (c) Martin Schröder <m.schroeder2007@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KoolKode\Util;

/**
 * Uses multiple sources of randomness and mixes them together using an approach based on HMAC.
 *
 * The code is partially taken from and heavily inspired by <b>RamdomLib</b> created by <b>Anthony Ferrara</b>.
 *
 * Only a limited subset of RandomLib is being provided, consider switching to RandomLib for
 * a more sophisticated implementation.
 *
 * @author Martin Schröder
 */
class RandomGenerator implements RandomGeneratorInterface
{
    protected static $rng;
    protected static $random;
    protected static $urandom;
    protected static $openSSL;
    protected static $capicom;
    protected static $mcrypt;

    protected $hash;

    public function __construct($hash = 'sha512')
    {
        $hash = (string)$hash;

        if (!in_array($hash, $algos = hash_algos(), true)) {
            sort($algos);

            throw new \InvalidArgumentException(sprintf(
                'Hash algorithm "%s" is not available, supported algorithms are %s',
                $hash,
                implode(', ', array_map(function ($alog) {
                    return '"' . $alog . '"';
                }, $algos))
            ));
        }

        $this->hash = $hash;
    }

    public function generateHexString($length, $strength = self::STRENGTH_MEDIUM)
    {
        return bin2hex($this->generateRandom($length, $strength));
    }

    protected function generateRandom($length, $strength)
    {
        $length = (int)$length;
        $strength = (int)$strength;

        if ($length < 1 || $length > 10000) {
            throw new \InvalidArgumentException(sprintf('Unable to generate %d random bytes', $length));
        }

        if (self::$rng === NULL) {
            self::$rng = function_exists('random_bytes');
        }

        // Use PHP CSPRNG exclusively if available:
        if (self::$rng) {
            return random_bytes($length);
        }

        if (self::$random === NULL) {
            self::$random = @is_readable('/dev/random');
        }

        if (self::$urandom === NULL) {
            self::$urandom = @is_readable('/dev/urandom');
        }

        if (self::$openSSL === NULL) {
            self::$openSSL = @function_exists('openssl_random_pseudo_bytes');
        }

        if (self::$capicom === NULL) {
            self::$capicom = @class_exists('COM', false);
        }

        $parts = [];

        // rand:
        if ($strength >= (defined('S_ALL') ? self::STRENGTH_LOW : self::STRENGTH_VERY_LOW)) {
            $str = '';

            for ($i = 0; $i < $length; $i++) {
                $str .= chr((rand() ^ rand()) % 255);
            }

            $parts[] = $str;
        }

        // MT rand:
        if ($strength >= (defined('S_ALL') ? self::STRENGTH_MEDIUM : self::STRENGTH_LOW)) {
            $str = '';

            for ($i = 0; $i < $length; $i++) {
                $str .= chr((mt_rand() ^ mt_rand()) % 256);
            }

            $parts[] = $str;
        }

        // uniquid:
        if ($strength >= self::STRENGTH_LOW) {
            $str = '';

            while (strlen($str) < $length) {
                $str = uniqid($str, true);
            }

            $parts[] = substr($str, 0, $length);
        }

        // High security from /dev/random
        if (self::$random && $strength >= self::STRENGTH_HIGH) {
            $fp = @fopen('/dev/random', 'rb');

            if ($fp !== false) {
                try {
                    if (function_exists('stream_set_read_buffer')) {
                        stream_set_read_buffer($fp, 0);
                    }

                    $parts[] = fread($fp, $length);
                } finally {
                    @fclose($fp);
                }
            }
        }

        // Medium security /dev/urandom
        if (self::$urandom && $strength >= self::STRENGTH_MEDIUM) {
            $fp = @fopen('/dev/urandom', 'rb');

            if ($fp !== false) {
                try {
                    if (function_exists('stream_set_read_buffer')) {
                        stream_set_read_buffer($fp, 0);
                    }

                    $parts[] = fread($fp, $length);
                } finally {
                    @fclose($fp);
                }
            }
        }

        // Use CAPICOM when running on Windows...
        if (self::$capicom && $strength >= self::STRENGTH_MEDIUM) {
            try {
                $ref = new \ReflectionClass('COM');
                $capi = $ref->newInstance('CAPICOM.Utilities.1');
                $parts[] = str_pad(base64_decode($capi->GetRandom($length, 0)), $length, chr(0));
            } catch (\Exception $e) {
                // Safe to ignore this.
            }
        }

        // Allways use OpenSSL when it is available no matter what strength has been selected.
        if (self::$openSSL) {
            $parts[] = openssl_random_pseudo_bytes($length);
        }

        if (empty($parts)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate a %u ramdom bytes of strength %u due to missing sources of randomness',
                $strength,
                $length
            ));
        }

        // Length of the generated random byte string.
        $length = strlen($parts[0]);

        $parts = $this->normalizeParts($parts, strlen(hash($this->hash, '', true)));

        // Number of parts being mixed together.
        $partCount = count($parts);

        // Number of chunks to be hashed for each part.
        $chunkCount = count($parts[0]);

        $result = '';

        for ($offset = 0, $i = 0; $i < $chunkCount; $i++) {
            $stub = $parts[$offset][$i];

            for ($j = 1; $j < $partCount; $j++) {
                $key = $parts[($j + $offset) % $partCount][$i];

                if ($j % 2 == 1) {
                    $stub ^= hash_hmac($this->hash, $stub, $key, true);
                } else {
                    $stub ^= hash_hmac($this->hash, $key, $stub, true);
                }
            }

            $result .= $stub;
            $offset = ($offset + 1) % $partCount;
        }

        return substr($result, 0, $length);
    }

    /**
     * Normalize the part array and split it block part size.
     *
     * @param array <string> $parts The random byte strings to be mixed together.
     * @param integer $blockSize The block size (number of bytes) of the underlying hash function.
     * @return array<array<string>>
     */
    protected function normalizeParts(array $parts, $blockSize)
    {
        // Determine byte count of longest part.
        $length = max(array_map('strlen', $parts));

        // Increase length to match a multiple of the hash functions block size when needed.
        if ($length % $blockSize) {
            $length += $blockSize - ($length % $blockSize);
        }

        // Split each part into chunks that have a length equal to the hash functions block size.
        foreach ($parts as & $part) {
            $part = str_split(str_pad($part, $length, chr(0)), $blockSize);
        }

        return $parts;
    }

    public function generateRaw($length, $strength = self::STRENGTH_MEDIUM)
    {
        return $this->generateRandom($length, $strength);
    }
}
