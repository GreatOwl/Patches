<?php
namespace src\Service\Database;

use TallTree\Roots\Service\Database\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function createPDOFactory()
    {
        $pdoFactory = $this->getMockBuilder('TallTree\Roots\Service\Database\PdoFactory')
            ->disableOriginalConstructor()
            ->getMock();
        return $pdoFactory;
    }

    private function createPDO()
    {
        $pdo = $this->getMockBuilder('mockPDO')
            ->setMethods(['prepare', 'exec', 'query'])
            ->getMock();
        return $pdo;
    }

    public function testSetGetConnection()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';
        $name = 'db name';

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password,
            $name
        );

        $connection->setConnection($pdo, false);
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertFalse($connection->isConnectedToDB());

        $connection->setConnection($pdo, true);
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertTrue($connection->isConnectedToDB());
    }

    public function testGetConnectionNoDatabaseRequestedSuccess()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';

        $dsn1 = sprintf(Connection::PDO_DSN_NO_DATABASE, $type, $server);
        $pdoFactoryCount = 0;
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn1), $this->equalTo($username), $this->equalTo($password))
            ->will($this->returnValue($pdo));

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password
        );

        $this->assertFalse($connection->isConnectedToDB());
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertFalse($connection->isConnectedToDB());
    }

    /**
     * @expectedException \PDOException
     * @expectedExceptionMessage nope!
     */
    public function testGetConnectionNoDatabaseRequestedFails()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();
        $errorMessage = 'nope!';

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';

        $dsn1 = sprintf(Connection::PDO_DSN_NO_DATABASE, $type, $server);
        $pdoFactoryCount = 0;
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn1), $this->equalTo($username), $this->equalTo($password))
            ->will($this->throwException(new \PDOException($errorMessage)));

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password
        );

        $this->assertFalse($connection->isConnectedToDB());
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertFalse($connection->isConnectedToDB());
    }

    public function testGetConnectionDatabaseRequestedSuccess()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';
        $name = 'db name';

        $dsn1 = sprintf(Connection::PDO_DSN_DATABASE, $type, $name, $server);
        $pdoFactoryCount = 0;
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn1), $this->equalTo($username), $this->equalTo($password))
            ->will($this->returnValue($pdo));

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password,
            $name
        );

        $this->assertFalse($connection->isConnectedToDB());
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertTrue($connection->isConnectedToDB());
    }

    public function testGetConnectionDatabaseRequestedCreatesSuccessfully()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();
        $errorMessage = 'nope!';

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';
        $name = 'db name';

        $dsn1 = sprintf(Connection::PDO_DSN_DATABASE, $type, $name, $server);
        $dsn2 = sprintf(Connection::PDO_DSN_NO_DATABASE, $type, $server);
        $pdoFactoryCount = 0;
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn1), $this->equalTo($username), $this->equalTo($password))
            ->will($this->throwException(new \PDOException($errorMessage)));
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn2), $this->equalTo($username), $this->equalTo($password))
            ->will($this->returnValue($pdo));

        $pdo->expects($this->once())
            ->method('exec')
            ->with($this->equalTo(sprintf(Connection::SQL_CREATE, $name)));
        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo(sprintf(Connection::SQL_USE, $name)));

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password,
            $name
        );

        $this->assertFalse($connection->isConnectedToDB());
        $this->assertEquals($pdo, $connection->getConnection());
        $this->assertTrue($connection->isConnectedToDB());
        $this->assertEquals([$errorMessage], $connection->getErrors());
    }

    /**
     * @expectedException \PDOException
     * @expectedException nope!\nnope!
     */
    public function testGetConnectionDatabaseRequestedCreatesFails()
    {
        $pdo = $this->createPDO();
        $pdoFactory = $this->createPDOFactory();
        $errorMessage = 'nope!';

        $type = 'db type';
        $server = 'server connection string';
        $username = 'db username';
        $password = 'db password';
        $name = 'db name';

        $dsn1 = sprintf(Connection::PDO_DSN_DATABASE, $type, $name, $server);
        $dsn2 = sprintf(Connection::PDO_DSN_NO_DATABASE, $type, $server);
        $pdoFactoryCount = 0;
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn1), $this->equalTo($username), $this->equalTo($password))
            ->will($this->throwException(new \PDOException($errorMessage)));
        $pdoFactory->expects($this->at($pdoFactoryCount++))
            ->method('createPDO')
            ->with($this->equalTo($dsn2), $this->equalTo($username), $this->equalTo($password))
            ->will($this->throwException(new \PDOException($errorMessage)));

        $connection = new Connection(
            $pdoFactory,
            $type,
            $server,
            $username,
            $password,
            $name
        );

        $this->assertFalse($connection->isConnectedToDB());
        $connection->getConnection();
    }

}
