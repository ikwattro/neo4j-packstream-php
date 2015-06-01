<?php
/*
 * This file is part of the Neo4j PackStream package.
 *
 * (c) Christophe Willemsen <willemsen.christophe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\PackStream\PackStream;

class Packer
{
    const SIGNATURE_RUN = 0x10;
    const SIGNATURE_PULL_ALL = 0x3f;

    const STRUCTURE_TINY = 0xb0;

    const TEXT_TINY = 0x80;
    const TEXT_REGULAR = 0xd0;

    const MAP_TINY = 0xa0;

    const SIZE_TINY = 16;

    const MISC_ZERO = 0x00;

    public function pack($v)
    {
        $stream = '';
        if (is_string($v)) {
            $v = utf8_encode($v);
            $l = mb_strlen($v, 'ASCII');
            $stream .= $this->getTextMarker($l);
            $stream .= $v;
        } elseif (is_array($v)) {
            $size = count($v);
            $stream .= $this->getMapMarker($size);
        }

        return $stream;
    }

    public function getTextMarker($length)
    {
        if ($length < self::SIZE_TINY) {
            return hex2bin(dechex(self::TEXT_TINY + $length));
        } else {
            $marker = chr(self::TEXT_REGULAR);
            $marker .= pack('c', $length);

            return $marker;
        }
    }

    public function getMapMarker($length)
    {
        if ($length < self::SIZE_TINY) {
            return hex2bin(dechex(self::MAP_TINY + $length));
        }
    }

    public function getRunSignature()
    {
        return chr(self::SIGNATURE_RUN);
    }

    public function getEndSignature()
    {
        return chr(self::MISC_ZERO) . chr(self::MISC_ZERO);
    }

    public function getPullAllMessage()
    {
        $msg = $this->getStructureMarker(0);
        $msg .= chr(self::SIGNATURE_PULL_ALL);
        $length = $this->getSizeMarker($msg);

        return $length . $msg . $this->getEndSignature();
    }

    public function getSizeMarker($stream)
    {
        $size = mb_strlen($stream, 'ASCII');

        return pack('n', $size);
    }

    public function getStructureMarker($size)
    {
        return chr(self::STRUCTURE_TINY + $size);
    }

    public function packBigEndian($v)
    {
        return pack('N', $v);
    }

    public function getStandardQueryStructureMarker()
    {
        return hex2bin(dechex(self::STRUCTURE_TINY + 2));
    }
}