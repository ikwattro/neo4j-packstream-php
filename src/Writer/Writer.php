<?php
/*
 * This file is part of the Neo4j PackStream package.
 *
 * (c) Christophe Willemsen <willemsen.christophe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\PackStream\Writer;

use Neo4j\PackStream\PackStream\Packer;
use Neo4j\PackStream\IO\Socket;
use Neo4j\PackStream\Version\Handshaker;

class Writer
{
    private $packer;

    private $io;

    private $handshaker;

    private $messages = [];

    public function __construct(Socket $socket)
    {
        $this->packer = new Packer();
        $this->io = $socket;
        $this->handshaker = new Handshaker($this->io, $this->packer);
    }

    public function doHandShake()
    {
        return $this->handshaker->handshake();
    }

    public function writeQuery($query, array $params = array())
    {
        $msg = $this->packer->getStandardQueryStructureMarker();
        $msg .= $this->packer->getRunSignature();
        $msg .= $this->packer->pack($query);
        $msg .= $this->packer->pack($params);
        $message = $this->packer->getSizeMarker($msg);
        $message .= $msg;
        $pullAll = $this->packer->getPullAllMessage();
        $message .= $this->packer->getSizeMarker($pullAll);
        $message .= $pullAll;
        $message .= $this->packer->getEndSignature();
        $this->messages[] = $message;
    }

    public function flush()
    {
        if (!$this->io->isConnected()) {
            $this->doHandShake();
        }
        foreach ($this->messages as $message) {
            $this->io->write($message);
        }
        $this->io->close();
    }
}