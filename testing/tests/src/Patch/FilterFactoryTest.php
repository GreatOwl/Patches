<?php
namespace src\Patch;

use TallTree\Roots\Patch\FilterFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
{

    private function createCollection()
    {
        $collection = $this->getMockBuilder('TallTree\Roots\Patch\Model\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        return $collection;
    }

    private function createInstall()
    {
        $install = $this->getMockBuilder('TallTree\Roots\Install\Model\Install')
            ->disableOriginalConstructor()
            ->getMock();
        return $install;
    }

    private function createPatch()
    {
        $patch = $this->getMockBuilder('TallTree\Roots\Patch\Model\Patch')
            ->disableOriginalConstructor()
            ->getMock();
        return $patch;
    }

    public function testUnMatchedFilterCollectionEmpty()
    {
        $patched = $this->createCollection();
        $patched->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([])));

        $patch = $this->createPatch();
        $filterFactory = new FilterFactory();

        $filter = $filterFactory->findUnmatched($patched);
        $this->assertTrue($filter($patch));
    }

    public function findUnmatchedProvider()
    {
        return [
            'statements match' => [
                'patchStatement' => 'a',
                'patchQuery' => 'b',
                'usedPatchStatement' => 'a',
                'usedPatchQuery' => 'c',
                'expected' => true
            ],
            'queries match' => [
                'patchStatement' => 'a',
                'patchQuery' => 'b',
                'usedPatchStatement' => 'c',
                'usedPatchQuery' => 'b',
                'expected' => true
            ],
            'statements and queries matche' => [
                'patchStatement' => 'a',
                'patchQuery' => 'b',
                'usedPatchStatement' => 'a',
                'usedPatchQuery' => 'b',
                'expected' => false
            ],
            'query matches statement' => [
                'patchStatement' => 'a',
                'patchQuery' => 'b',
                'usedPatchStatement' => 'b',
                'usedPatchQuery' => 'c',
                'expected' => false
            ],
            'no matches' => [
                'patchStatement' => 'a',
                'patchQuery' => 'b',
                'usedPatchStatement' => 'c',
                'usedPatchQuery' => 'd',
                'expected' => true
            ],
        ];
    }

    /**
     * @dataProvider findUnmatchedProvider
     */
    public function testUnMatchedFilterCollectionFailsFirstStatement(
        $patchStatement,
        $patchQuery,
        $usedPatchStatement,
        $usedPatchQuery,
        $expected
    ) {
        $usedPatch = $this->createPatch();
        $usedPatch->expects($this->once())
            ->method('getPatch')
            ->willReturn($usedPatchStatement);
        $usedPatch->expects($this->once())
            ->method('getQuery')
            ->willReturn($usedPatchQuery);
        $patched = $this->createCollection();
        $patched->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$usedPatch])));

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getPatch')
            ->willReturn($patchStatement);
        $patch->expects($this->once())
            ->method('getQuery')
            ->willReturn($patchQuery);

        $filterFactory = new FilterFactory();

        $filter = $filterFactory->findUnmatched($patched);
        $this->assertEquals($expected, $filter($patch));
    }

    public function afterInstallProvider()
    {
        return [
            'original install' => ['patchNumber' => 0, 'installNumber' => 0, 'expected' => true],
            'original install new patch' => ['patchNumber' => 0, 'installNumber' => 0, 'expected' => true],
            'original install crazy patch' => ['patchNumber' => -1, 'installNumber' => 0, 'expected' => false],
            'recent install new patch' => ['patchNumber' => 9, 'installNumber' => 8, 'expected' => true],
            'recent install old patch' => ['patchNumber' => 6, 'installNumber' => 8, 'expected' => false]
        ];
    }

    /**
     * @dataProvider afterInstallProvider
     * @param $patchNumber
     * @param $installPatchNumber
     * @param $expected
     */
    public function testFindeAfterInstallFilter(
        $patchNumber,
        $installPatchNumber,
        $expected
    ) {
        $patch = $this->createPatch();
        $install = $this->createInstall();

        $patch->expects($this->once())
            ->method('getPatch')
            ->will($this->returnValue($patchNumber));
        $install->expects($this->once())
            ->method('getPatch')
            ->will($this->returnValue($installPatchNumber));

        $filterFactory = new FilterFactory();

        $filter = $filterFactory->findAfterInstall($install);

        $this->assertEquals($expected, $filter($patch));
    }
}
