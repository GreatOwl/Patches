<?php
namespace TallTree\Roots\Service\Database;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    private function createPDO()
    {
        $pdo = $this->getMockBuilder('mockPDO')
            ->setMethods(['prepare'])
            ->getMock();
        return $pdo;
    }

    private function createPDOStatement()
    {
        $statement = $this->getMockBuilder('PDOStatement')
            ->disableOriginalConstructor()
            ->getMock();
        return $statement;
    }

    private function createConnection($pdo)
    {
        $connection = $this->getMockBuilder('TallTree\Roots\Service\Database\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($pdo));

        return $connection;
    }

    public function testPatch()
    {
        $pdo = $this->createPDO();
        $pdoStatement = $this->createPDOStatement();
        $connection = $this->createConnection($pdo);

        $statement = 'some sql statement';
        $returnInfo = ['info in an array'];

        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo($statement))
            ->will($this->returnValue($pdoStatement));

        $pdoStatement->expects($this->once())
            ->method('execute');

        $pdoStatement->expects($this->once())
            ->method('errorInfo')
            ->will($this->returnValue($returnInfo));

        $query = new Query($connection);

        $this->assertEquals($returnInfo, $query->patch($statement));
    }

    public function inputProvider()
    {
        return [
            'all numbers' => [
                'input' => ['a' => 1, 'b' => 2, 'c' => 1203.97 ],
                'expected' => ['a' => \PDO::PARAM_INT, 'b' => \PDO::PARAM_INT, 'c' => \PDO::PARAM_STR]
            ],
            'all booleans' => [
                'input' => ['a' => true, 'b' => false, 'c' => true],
                'expected' => ['a' => \PDO::PARAM_BOOL, 'b' => \PDO::PARAM_BOOL, 'c' => \PDO::PARAM_BOOL]
            ],
            'all strings' => [
                'input' => ['a' => 'string 1', 'b' => 'true', 'c' => '3'],
                'expected' => ['a' => \PDO::PARAM_STR, 'b' => \PDO::PARAM_STR, 'c' => \PDO::PARAM_STR]
            ],
            'mixed types' => [
                'input' => ['a' => 'string 1', 'b' => true, 'c' => 3],
                'expected' => ['a' => \PDO::PARAM_STR, 'b' => \PDO::PARAM_BOOL, 'c' => \PDO::PARAM_INT]
            ]
        ];
    }


    /**
     * @param $input
     * @param $expected
     * @dataProvider inputProvider
     */
    public function testRead($input, $expected)
    {
        $pdo = $this->createPDO();
        $pdoStatement = $this->createPDOStatement();
        $connection = $this->createConnection($pdo);

        $statement = 'some sql statement';
        $returnInfo = ['info in an array'];

        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo($statement))
            ->will($this->returnValue($pdoStatement));

        $statementCount = 0;
        foreach ($input as $field => $value) {
            $pdoStatement->expects($this->at($statementCount++))
                ->method('bindValue')
                ->with($this->equalTo(":$field"), $this->equalTo($value), $this->equalTo($expected[$field]));
        }

        $pdoStatement->expects($this->once())
            ->method('execute');

        $pdoStatement->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($returnInfo));

        $query = new Query($connection);

        $this->assertEquals($returnInfo, $query->read($statement, $input));
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider inputProvider
     */
    public function testWrite($input, $expected)
    {
        $pdo = $this->createPDO();
        $pdoStatement = $this->createPDOStatement();
        $connection = $this->createConnection($pdo);

        $statement = 'some sql statement';
        $returnInfo = 3;

        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->equalTo($statement))
            ->will($this->returnValue($pdoStatement));

        $statementCount = 0;
        foreach ($input as $field => $value) {
            $pdoStatement->expects($this->at($statementCount++))
                ->method('bindValue')
                ->with($this->equalTo(":$field"), $this->equalTo($value), $this->equalTo($expected[$field]));
        }

        $pdoStatement->expects($this->once())
            ->method('execute');

        $pdoStatement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue($returnInfo));

        $query = new Query($connection);

        $this->assertEquals($returnInfo, $query->write($statement, $input));
    }
}
