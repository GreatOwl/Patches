<?php
namespace TallTree\Roots\Service\Database;

use PDO;
use PDOException;

class Connection
{
    const PDO_DSN_DATABASE = "%s:dbname=%s;host=%s";
    const PDO_DSN_NO_DATABASE = "%s:host=%s";

    const SQL_CREATE = "CREATE DATABASE IF NOT EXISTS %s";
    const SQL_USE = "use %s";

    /**
     * @var PdoFactory $pdoFactory
     */
    private $pdoFactory;
    private $type;
    private $server;
    private $username;
    private $password;
    private $name;

    /**
     * @var PDO $connection
     */
    private $connection = null;

    private $isConnectedToDB = false;

    /**
     * @var PDOException[] $errors
     */
    private $errors = [];

    public function __construct(
        PdoFactory $pdoFactory,
        $type,
        $server,
        $username,
        $password,
        $name = null
    ) {
        $this->pdoFactory = $pdoFactory;
        $this->type = $type;
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
    }

    public function getConnection()
    {
        if (is_null($this->connection)) {
            $this->setConnection($this->connect(), $this->isConnectedToDB());
        }

        return $this->connection;
    }

    public function setConnection($connection, $isConnectedToDB = true)
    {
        $this->connection = $connection;
        $this->isConnectedToDB = $isConnectedToDB;
    }

    public function isConnectedToDB()
    {
        return $this->isConnectedToDB;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function connect()
    {
        if (!is_null($this->name)) {
            return $this->connectToDatabase();
        } else {
            return $this->connectWithoutDatabase();
        }
    }

    protected function connectToDatabase()
    {
        $dsn = sprintf(static::PDO_DSN_DATABASE, $this->type, $this->name, $this->server);
        try {
            $connection = $this->pdoFactory->createPDO($dsn, $this->username, $this->password);//new PDO($dsn, $this->username, $this->password);
            $this->isConnectedToDB = true;
            return $connection;
        } catch (PDOException $error) {
            $this->errors[] = $error->getMessage();
            sleep(1);
            return $this->connectWithoutDatabase();
        }
    }

    protected function connectWithoutDatabase()
    {
        $dsn = sprintf(static::PDO_DSN_NO_DATABASE, $this->type, $this->server);
        try {
            $connection = $this->pdoFactory->createPDO($dsn, $this->username, $this->password);//new PDO($dsn, $this->username, $this->password);
            $connection = $this->attemptToCreateDatabase($connection);
            return $connection;
        } catch (PDOException $error) {
            $this->errors[] = $error->getMessage();
            throw new PDOException($this->concatErrors());
        }
    }

    protected function attemptToCreateDatabase($connection)
    {
        if (!is_null($this->name)) {
            $dbname = $this->name;
            $connection->exec(sprintf(static::SQL_CREATE, $dbname));
            $connection->query(sprintf(static::SQL_USE, $dbname));

            $this->isConnectedToDB = true;
        }

        return $connection;
    }

    protected function concatErrors()
    {
        $output = '';
        foreach ($this->errors as $error) {
            $output .= $error . "\n";
        }
        return $output;
    }
}
