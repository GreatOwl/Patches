<?php
namespace TallTree\Roots\Service\Database;

use PDO;

class PdoFactory
{
    public function createPDO($dsn, $username, $password)
    {
        return new PDO($dsn, $username, $password);
    }
}
