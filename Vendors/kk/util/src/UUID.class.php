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
 * Encapsulates a UUID string.
 *
 * @author Martin Schröder
 */
class UUID implements \JsonSerializable
{
    /**
     * UUID version 3: based on an MD5-hash, deprecated, use UUIDv5 instead
     *
     * @var integer
     */
    const V3 = 3;

    /**
     * UUID version 4: based on pseudo-random bytes
     *
     * @var integer
     */
    const V4 = 4;

    /**
     * UUID version 5: based on SHA1-hash.
     *
     * @var integer
     */
    const V5 = 5;

    /**
     * UUID pattern: Validates any version of a UUID
     *
     * @var string
     */
    const PATTERN_UUID = "'^[0-9a-f]{8}-[0-9a-f]{4}-(?<type>[0-9a-f])[0-9a-f]{3}-(?<version>[0-9a-f])[0-9a-f]{3}-[0-9a-f]{12}$'";

    /**
     * UUID pattern: Validates UUIDv3
     *
     * @var string
     */
    const PATTERN_UUID_V3 = "'^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'";

    /**
     * UUID pattern: Validates UUIDv4
     *
     * @var string
     */
    const PATTERN_UUID_V4 = "'^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'";

    /**
     * UUID pattern: Validates UUIDv5
     *
     * @var string
     */
    const PATTERN_UUID_V5 = "'^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'";

    const NS_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    const NS_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    const NS_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    const NS_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

    protected $value;

    public function __construct($uuid)
    {
        if ($uuid instanceof self) {
            $this->value = $uuid->value;
        } else {
            if (is_resource($uuid)) {
                $uuid = stream_get_contents($uuid);
            }

            $in = (string)$uuid;

            switch (strlen($in)) {
                case 16:
                    $this->value = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($in), 4));
                    break;
                case 32:
                    $this->value = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(strtolower($in), 4));
                    break;
                case 36:
                    $this->value = strtolower($in);
                    break;
            }

            if (!preg_match(self::PATTERN_UUID, $this->value)) {
                throw new \InvalidArgumentException(sprintf('Invalid UUID: %s', $in));
            }
        }
    }

    /**
     * Creates a UUIDv4 from random bytes using mt_rand().
     *
     * @param RandomGeneratorInterface $random Optional random generator, algorith is based on mt_rand() if ommitted.
     * @return UUID
     */
    public static function createRandom(RandomGeneratorInterface $random = NULL)
    {
        if ($random !== NULL) {
            $rand = str_split($random->generateHexString(32), 4);

            return new static(sprintf(
                '%s%s-%s-%04x-%04x-%s%s%s',
                $rand[0], $rand[1],
                $rand[2],
                (hexdec($rand[3]) % 0x0FFF) | 0x4000,
                (hexdec($rand[4]) % 0x3FFF) | 0x8000,
                $rand[5], $rand[6], $rand[7]
            ));
        }

        return new static(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        ));
    }

    /**
     * Create a name based UUID using a SHA1 hash and a namespace UUID.
     *
     * @param UUID $namespace
     * @param string $name
     * @return string
     */
    public static function createNameBased(UUID $namespace, $name)
    {
        $hash = str_split(sha1($namespace->toBinary() . $name), 4);

        return new static(sprintf(
            '%04s%04s-%04s-%04x-%04x-%04s%04s%04s',
            $hash[0], $hash[1],
            $hash[2],
            hexdec($hash[3]) & 0x0FFF | 0x5000,
            hexdec($hash[4]) & 0x3FFF | 0x8000,
            $hash[5], $hash[6], $hash[7]
        ));
    }

    /**
     * Convert the UUID into a sequence of 16 bytes.
     *
     * @return string
     */
    public function toBinary()
    {
        return pack('H*', str_replace('-', '', $this->value));
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function toString($compact = false)
    {
        if ($compact) {
            return str_replace('-', '', $this->value);
        }

        return $this->value;
    }

    /**
     * Get the type of the given UUID.
     *
     * @return integer
     *
     * @throws \InvalidArgumentException When the given UUID is invalid.
     */
    public function getVersion()
    {
        $m = NULL;
        preg_match(self::PATTERN_UUID, $this->value, $m);

        return (int)$m['type'];
    }
}
