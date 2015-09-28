#!/usr/bin/env php
<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use TallTree\Roots\Install\Model\Service\Database\MySqlMap;
use TallTree\Roots\Install\Model\Service\File\FileMap;
use TallTree\Roots\Install\Factory;
use TallTree\Roots\Install\Repository;
use TallTree\Roots\Install\Installer;
use TallTree\Roots\Service\Database\Connection;
use TallTree\Roots\Service\Database\PdoFactory;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;
use TallTree\Roots\Service\Transform\NameSpaces;

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
$repository = new Repository($dbMap, $fileMap, $factory);
$worker = new Installer(
    $repository,
    $query,
    $fileHandle,
    $dbMap,
    $fileMap,
    $factory,
    $transformer
);

$worker->installAll();

