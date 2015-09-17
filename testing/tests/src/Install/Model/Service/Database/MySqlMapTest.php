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

    public function testApplyInstallWithNoFields()
    {
        $fields = [];

        $allSet = '';

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query);

        $map->applyInstall($install);
    }

    public function testApplyInstallWithOneField()
    {
        $fields = ['foo' => 'stuff'];

        $fooSet = sprintf(MySqlMap::SET_VALUE, 'foo', 'foo');
        $allSet = $fooSet;

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query);

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

        $queryString = sprintf(MySqlMap::APPLY_PATCH, $allSet);

        $query = $this->createQuery();
        $install = $this->createInstall();

        $install->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($fields));

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($fields));

        $map = new MySqlMap($query);

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

        $queryString = sprintf(MySqlMap::UPDATE_PATCH, $allSet);

        $query = $this->createQuery();
        $originalInstall = $this->createInstall();
        $newInstall = $this->createInstall();

        $originalInstall->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($oldFields));
        $newInstall->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($newFields));

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($queryString), $this->equalTo($diffFields));

        $map = new MySqlMap($query);

        $map->updateInstall($originalInstall, $newInstall);
    }

    public function testGetInstallEmptyResults()
    {
        $table = 'something';
        $queryString = sprintf(MySqlMap::SELECT_PATCHES_TABLE, $table);

        $query = $this->createQuery();

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([]));

        $map = new MySqlMap($query);

        $this->assertEquals(['table' => $table, 'install' => ''], $map->getInstall($table));
    }

    public function testGetInstallSingleResultEmptyInstall()
    {
        $table = 'something';
        $results = ['results' => 'stuff'];
        $queryString = sprintf(MySqlMap::SELECT_PATCHES_TABLE, $table);
        $queryShowTable = sprintf(MySqlMap::SHOW_CREATE_TABLE, $table);

        $query = $this->createQuery();

        $query->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([$results]));
        $query->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo($queryShowTable), $this->equalTo([]))
            ->will($this->returnValue(''));

        $map = new MySqlMap($query);

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

        $query->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo($queryString), $this->equalTo(['table' => $table]))
            ->will($this->returnValue([$results]));
        $query->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo($queryShowTable), $this->equalTo([]))
            ->will($this->returnValue([['Create Table' => $installSQL]]));

        $map = new MySqlMap($query);

        $this->assertEquals($results, $map->getInstall($table));
    }

}
