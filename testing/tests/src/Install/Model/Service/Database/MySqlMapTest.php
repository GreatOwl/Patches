<?php
namespace TallTree\Roots\Install\Model\Service\Database;

class MySqlMapTest extends \PHPUnit_Framework_TestCase
{

    private function createQuery()
    {
        $query = $this->getMockBuilder('TallTree\Roots\Service\Database\Query')
            ->disableOriginalConstructor()
            ->getMock();
        return $query;
    }

    private function createInstall()
    {
        $install = $this->getMockBuilder('TallTree\Roots\Install\Model\Install')
            ->disableOriginalConstructor()
            ->getMock();
        return $install;
    }

    private function createNameSpacesTransform()
    {
        $nameSpaces = $this->getMockBuilder('TallTree\Roots\Service\Transform\NameSpaces')
            ->disableOriginalConstructor()
            ->getMock();
        return $nameSpaces;
    }

    public function testApplyInstallWithNoFields()
    {
        $fields = [];

        $allSet = '';

        $columns = [];
        $resultingColumnQuery = 'resultingQuery';

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $nameSpaces->expects($this->at(0))
            ->method('getAppNameSpace')
            ->willReturn('root_');
        $nameSpaces->expects($this->at(1))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::SHOW_COLUMNS), $this->equalTo(false))
            ->willReturn($resultingColumnQuery);
        $nameSpaces->expects($this->at(2))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::APPLY_PATCH), $this->equalTo(false))
            ->willReturn($queryString);

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($resultingColumnQuery))
            ->willReturn($columns);
        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query, $nameSpaces);

        $map->applyInstall($install);
    }

    public function testApplyInstallWithOneField()
    {
        $fields = ['foo' => 'stuff'];

        $fooSet = sprintf(MySqlMap::SET_VALUE, 'foo', 'foo');
        $allSet = $fooSet;

        $columns = [['Field' => 'foo', 'Type' => 'thing']];
        $resultingColumnQuery = 'resultingQuery';

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $nameSpaces->expects($this->at(0))
            ->method('getAppNameSpace')
            ->willReturn('root_');
        $nameSpaces->expects($this->at(1))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::SHOW_COLUMNS), $this->equalTo(false))
            ->willReturn($resultingColumnQuery);
        $nameSpaces->expects($this->at(2))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::APPLY_PATCH), $this->equalTo(false))
            ->willReturn($queryString);

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($resultingColumnQuery))
            ->willReturn($columns);
        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query, $nameSpaces);

        $map->applyInstall($install);
    }

    public function testApplyInstallWithMultipleFields()
    {
        $fields = [
            'foo' => 'stuff',
            'bar' => 'junk',
            'baz' => 'garbage'
        ];

        $fooSet = sprintf(MySqlMap::SET_VALUE, 'foo', 'foo');
        $barSet = sprintf(MySqlMap::SET_VALUE, 'bar', 'bar');
        $bazSet = sprintf(MySqlMap::SET_VALUE, 'baz', 'baz');
        $allSet = $fooSet . ',' .  $barSet . ',' . $bazSet;

        $columns = [
            ['Field' => 'foo', 'Type' => 'thing'],
            ['Field' => 'bar', 'Type' => 'thing'],
            ['Field' => 'baz', 'Type' => 'thing']
        ];
        $resultingColumnQuery = 'resultingQuery';

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $nameSpaces->expects($this->at(0))
            ->method('getAppNameSpace')
            ->willReturn('root_');
        $nameSpaces->expects($this->at(1))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::SHOW_COLUMNS), $this->equalTo(false))
            ->willReturn($resultingColumnQuery);
        $nameSpaces->expects($this->at(2))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::APPLY_PATCH), $this->equalTo(false))
            ->willReturn($queryString);

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($resultingColumnQuery))
            ->willReturn($columns);
        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query, $nameSpaces);

        $map->applyInstall($install);
    }

    public function testUpdateInstallWithMultipleFields()
    {
        $newFields = [
            'foo' => 'stuff',
            'bar' => 'things',
            'baz' => 'garbage',
            'boo' => 'more things'
        ];

        $oldFields = [
            'foo' => 'stuff',
            'bar' => 'junk',
            'baz' => 'garbage',
        ];

        $diffFields = [
            'bar' => 'things',
            'boo' => 'more things',
            'id' => null
        ];

        $barSet = sprintf(MySqlMap::SET_VALUE, 'bar', 'bar');
        $booSet = sprintf(MySqlMap::SET_VALUE, 'boo', 'boo');
        $idSet = sprintf(MySqlMap::SET_VALUE, 'id', 'id');
        $allSet = $barSet . ',' .  $booSet . ',' . $idSet;

        $columns = [
            ['Field' => 'bar', 'Type' => 'thing'],
            ['Field' => 'boo', 'Type' => 'thing'],
            ['Field' => 'id', 'Type' => 'thing']
        ];
        $resultingColumnQuery = 'resultingQuery';

        $queryString = sprintf(MySqlMap::UPDATE_PATCH, $allSet);

        $query = $this->createQuery();
        $originalInstall = $this->createInstall();
        $newInstall = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $nameSpaces->expects($this->at(0))
            ->method('getAppNameSpace')
            ->willReturn('root_');
        $nameSpaces->expects($this->at(1))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::SHOW_COLUMNS), $this->equalTo(false))
            ->willReturn($resultingColumnQuery);
        $nameSpaces->expects($this->at(2))
            ->method('addNameSpaceToQuery')
            ->with($this->equalTo(MySqlMap::UPDATE_PATCH), $this->equalTo(false))
            ->willReturn($queryString);

        $originalInstall->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($oldFields));
        $newInstall->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($newFields));

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($resultingColumnQuery))
            ->willReturn($columns);
        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($diffFields));

        $map = new MySqlMap($query, $nameSpaces);

        $map->updateInstall($originalInstall, $newInstall);
    }

    public function testGetInstallEmptyResults()
    {
        $table = 'something';
        $queryString = sprintf(MySqlMap::SELECT_PATCHES_TABLE, $table);

        $query = $this->createQuery();
        $nameSpaces = $this->createNameSpacesTransform();

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([]));

        $map = new MySqlMap($query, $nameSpaces);

        $this->assertEquals(['table' => $table, 'install' => ''], $map->getInstall($table));
    }

    public function testGetInstallSingleResultEmptyInstall()
    {
        $table = 'something';
        $results = ['results' => 'stuff'];
        $queryString = sprintf(MySqlMap::SELECT_PATCHES_TABLE, $table);
        $queryShowTable = sprintf(MySqlMap::SHOW_CREATE_TABLE, $table);

        $query = $this->createQuery();
        $nameSpaces = $this->createNameSpacesTransform();

        $query->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([$results]));
        $query->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo($queryShowTable), $this->equalTo([]))
            ->will($this->returnValue(''));

        $map = new MySqlMap($query, $nameSpaces);

        $this->assertEquals($results, $map->getInstall($table));
    }

    public function testGetInstallSingleResultNotEmptyInstall()
    {
        $installSQL = "CREATE TABLE `install` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL DEFAULT '',
  `install` text NOT NULL,
  `patch` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=324871 DEFAULT CHARSET=utf8";

        $replacedInstallSQL = "CREATE TABLE `install` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL DEFAULT '',
  `install` text NOT NULL,
  `patch` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8";

        $table = 'something';
        $results = ['results' => 'stuff', 'install' => $replacedInstallSQL];
        $queryString = sprintf(MySqlMap::SELECT_PATCHES_TABLE, $table);
        $queryShowTable = sprintf(MySqlMap::SHOW_CREATE_TABLE, $table);

        $query = $this->createQuery();
        $nameSpaces = $this->createNameSpacesTransform();

        $query->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([$results]));
        $query->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo($queryShowTable), $this->equalTo([]))
            ->will($this->returnValue([['Create Table' => $installSQL]]));

        $map = new MySqlMap($query, $nameSpaces);

        $this->assertEquals($results, $map->getInstall($table));
    }

}
