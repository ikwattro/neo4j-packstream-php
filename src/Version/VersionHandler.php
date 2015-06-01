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

class VersionHandler
{
    private $versions = [];

    public function registerVersion($version)
    {
        if (!in_array($version, $this->versions)) {
            $this->versions[] = $version;
        }
    }

    public function hasVersion($version)
    {
        return in_array($version, $this->versions);
    }

    public function getVersions()
    {
        return array(1, 0, 0, 0);
    }
}