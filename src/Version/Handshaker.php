<?php
/*
 * This file is part of the Neo4j PackStream package.
 *
 * (c) Christophe Willemsen <willemsen.christophe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\PackStream\Version;

use Neo4j\PackStream\IO\Socket;
use Neo4j\PackStream\PackStream\Packer;
use Neo4j\PackStream\Version\VersionHandler;
use Neo4j\PackStream\Exception\Neo4jPackStreamHandshakeException;

class Handshaker
{
    private $io;

    private $packer;

    private $vh;

    public function __construct(Socket $io, Packer $packer)
    {
        $this->io = $io;
        $this->packer = $packer;
        $this->vh = new VersionHandler();
    }

    public function handshake()
    {
        $msg = '';
        foreach ($this->vh->getVersions() as $v) {
            $msg .= $this->packer->packBigEndian($v);
        }
        if (!$this->io->isConnected()) {
            $this->io->reconnect();
        }
        $this->io->write($msg);
        $response = unpack('N', $this->io->read(4));
        $version = $response[1];
        if (0 === $version) {
            throw new Neo4jPackStreamHandshakeException("No compatible version could be arranged during the handshake");
        }

        return $version;
    }
}