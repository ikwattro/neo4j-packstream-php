<?php
/*
 * This file is part of the Neo4j PackStream package.
 *
 * (c) Christophe Willemsen <willemsen.christophe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neo4j\PackStream;

use Neo4j\PackStream\IO\Socket;
use Neo4j\PackStream\Writer\Writer;

class Session
{
    private $io;

    private $writer;

    public function __construct($host, $port)
    {
        $this->io = new Socket($host, $port);
        $this->writer = new Writer($this->io);
    }

    public function runQuery($query, array $params = array())
    {
        $this->writer->writeQuery($query, $params);
        $this->writer->flush();
    }

    public function flush()
    {
        $this->writer->flush();
    }

    public function testHandShake()
    {
        return $this->writer->doHandShake();
    }
}