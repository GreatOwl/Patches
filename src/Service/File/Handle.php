<?php
namespace TallTree\Roots\Service\File;

use League\Flysystem\Filesystem;

class Handle
{
    private $filesystem;
    private $dbDir;
    public function __construct(
        Filesystem $filesystem,
        $dbDir
    ) {
        $this->filesystem = $filesystem;
        $this->dbDir = $dbDir;
    }

    public function getAllFilesInDir($directory)
    {
        $filePath = $this->dbDir . $directory;
        $files = $this->filesystem->listContents($filePath);
        $patches = [];
        foreach($files as $file) {
            if (strtolower($file['extension']) == "json") {
                $patches[] = $file['filename'];
            }
        }
        return $patches;
    }
}
