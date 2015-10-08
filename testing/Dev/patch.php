#!/usr/bin/env php
<?php

use TallTree\Roots\Service\Database\Connection;
use TallTree\Roots\Service\Database\PdoFactory;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;
use TallTree\Roots\Service\Transform\NameSpaces;
use TallTree\Roots\Patch\Model\Service\Database\MySqlMap;
use TallTree\Roots\Patch\Model\Service\File\FileMap;
use TallTree\Roots\Patch\Factory;
use TallTree\Roots\Patch\Repository;
use TallTree\Roots\Patch\Patcher;
use TallTree\Roots\Patch\FilterFactory;
use TallTree\Roots\Install\Repository as InstallRepository;
use TallTree\Roots\Install\Model\Service\File\FileMap as InstallFileMap;
use TallTree\Roots\Install\Model\Service\Database\MySqlMap as InstallMySqlMap;
use TallTree\Roots\Install\Factory as InstallFactory;
use TallTree\Roots\Install\Installer;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

$adapter = new Local(__DIR__ . '/../../');
$fileSystem = new Filesystem($adapter, [
    'visibility' => AdapterInterface::VISIBILITY_PRIVATE
]);

$configurationFile = $fileSystem->read('testing/Dev/conf.json');
$configuration = json_decode($configurationFile, true);

$database = $configuration['database'];
$dbDir = $configuration['directory'];

$connection = new Connection(
    new PdoFactory(),
    $database['type'],
    $database['server'],
    $database['username'],
    $database['password'],
    $database['name']
);

$query = new Query($connection);
$fileHandle = new Handle($fileSystem, $dbDir);
$transformer = new NameSpaces($connection, $configuration['namespaces']);
$dbMap = new MySqlMap($query, $transformer);
$fileMap = new FileMap($fileSystem, $transformer, $dbDir);
$factory = new Factory();
$filterFactory = new FilterFactory();
$repository = new Repository($dbMap, $fileMap, $factory);

$installDbMap = new InstallMySqlMap($query, $transformer);
$installFileMap = new InstallFileMap($fileSystem, $transformer, $dbDir);
$installFactory = new InstallFactory();
$installRepository = new InstallRepository($installDbMap, $installFileMap, $installFactory);
$installer = new Installer(
    $installRepository,
    $query,
    $fileHandle,
    $installDbMap,
    $installFileMap,
    $installFactory,
    $transformer
);

$worker = new Patcher(
    $repository,
    $filterFactory,
    $query,
    $fileHandle,
    $dbMap,
    $fileMap,
    $installRepository,
    $installer,
    $transformer
);

$worker->patchAll();

