<?php

namespace GreatOwl\Patches\Model\Patch;


use GreatOwl\Patches\Connect;
use GreatOwl\Patches\Container;
use PDO;

class Patch
{
    const INSTALL = "Install/patch.sql";
    const PATCH = "Patches/patch.phpd";
    const TABLE = "patches";

    /**
     * @var PDO $connection
     */
    private $connection;

    /**
     * @var array $parameters
     */
    private $parameters;

    /**
     * @var int $id
     */
    private $id;

    /**
     * @param Connect $connect
     * @param array $raw
     */
    public function __construct(Connect $connect, $raw = [])
    {
        $this->connection = $connect->getConnection();
        $this->parameters = new Container($raw);
    }

    public function getId()
    {
        //this may not be best... revisit
        if (is_null($this->id)) {
            $statement = $this->connection->prepare("Select id from patches WHERE table = :table AND patch = :patch");
            $statement->bindParam(':table', $this->getTable());
            $statement->bindParam(':patch', $this->getPatch());
            $statement->execute();
            $this->id = $statement->fetch();
        }

        return $this->id;
    }

    public function getTable()
    {
        return $this->parameters->get('table');
    }

    public function getPatch()
    {
        return $this->parameters->get('patch');
    }

    public function getQuery()
    {
        return $this->parameters->get('query');
    }

    public function getStatus()
    {
        return $this->parameters->get('status');
    }

    public function setStatus($status)
    {
        $this->parameters->set('status', $status);
    }

    public function save()
    {

    }

}
