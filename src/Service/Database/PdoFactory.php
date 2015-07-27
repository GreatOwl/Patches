<?php
namespace TallTree\Roots\Service\Database;

use PDO;

/**
 * Class PdoFactory
 *
 * @codeCoverageIgnore
 * Assumption: This elementary component of PDO is already tested and needs nothing further.
 */
class PdoFactory
{
    public function createPDO($dsn, $username, $password)
    {
        return new PDO($dsn, $username, $password);
    }
}
