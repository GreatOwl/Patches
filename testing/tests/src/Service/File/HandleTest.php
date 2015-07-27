<?php
namespace src\Service\File;

use TallTree\Roots\Service\File\Handle;

class HandleTest extends \PHPUnit_Framework_TestCase
{

    private function createFileSystem()
    {
        $fileSystem = $this->getMockBuilder('League\Flysystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        return $fileSystem;
    }

    public function fileProvider()
    {
        return [
            'no files' => [
                'fileArray' => [],
                'expeted' => []
            ],
            '1 file no patches' => [
                'fileArray' => [
                    [
                        'extension' => 'blah',
                        'filename' => 'nothing'
                    ]
                ],
                'expected' => []
            ],
            '1 file 1 patches' => [
                'fileArray' => [
                    [
                        'extension' => 'json',
                        'filename' => 'something'
                    ]
                ],
                'expected' => ['something']
            ],
            '2 files mixed patches' => [
                'fileArray' => [
                    [
                        'extension' => 'blah',
                        'filename' => 'nothing'
                    ],
                    [
                        'extension' => 'json',
                        'filename' => 'something'
                    ]
                ],
                'expected' => ['something']
            ],
            '2 files 2 patches' => [
                'fileArray' => [
                    [
                        'extension' => 'json',
                        'filename' => 'nothing'
                    ],
                    [
                        'extension' => 'json',
                        'filename' => 'something'
                    ]
                ],
                'expected' => ['nothing', 'something']
            ]
        ];
    }

    /**
     * @param $fileArray
     * @param $expected
     * @dataProvider fileProvider
     */
    public function testGetAllFilesInDirEmpty($fileArray, $expected)
    {
        $fileSystem = $this->createFileSystem();
        $dbDir = 'some/directory/';
        $loadDir = 'another/directory';
        $handle = new Handle($fileSystem, $dbDir);

        $fileSystem->expects($this->once())
            ->method('listContents')
            ->with($this->anything())
            ->will($this->returnValue($fileArray));

        $this->assertEquals($expected, $handle->getAllFilesInDir($loadDir));
    }
}
