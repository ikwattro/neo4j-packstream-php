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
        $queryChunk = $this->packer->getStandardQueryStructureMarker();
        $queryChunk .= $this->packer->getRunSignature();
        $queryChunk .= $this->packer->pack($query);
        $queryChunk .= $this->packer->pack($params);
        $this->messages[] = $this->packer->getSizeMarker($queryChunk) . $queryChunk . $this->packer->getEndSignature();
        $pullChunk = $this->packer->getPullAllMessage();
        $this->messages[] = $pullChunk;
    }

    public function flush()
    {
        if (!$this->io->isConnected()) {
            $this->doHandShake();
        }
        $stream = '';
        foreach ($this->messages as $message) {
            $stream .= $message;
        }
        $this->io->write($stream);

        $chunkSize = -1;
        $data = [];
        while ($chunkSize !== 0) {
            $chunkHeader = $this->io->read(2);
            $chunkSize = hexdec(unpack('H*', $chunkHeader)[1]);
            $this->io->read(2);
            var_dump($chunkSize);
            $data[] = $this->io->read($chunkSize);
            echo 'Chunk Size is ' . $chunkSize . "\n";
        }
        var_dump($data);

        $this->io->close();
    }
}