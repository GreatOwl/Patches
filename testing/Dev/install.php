#!/usr/bin/env php
<?php

use TallTree\Roots\Service\Database\Connection;
use TallTree\Roots\Service\Database\PdoFactory;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;
use TallTree\Roots\Install\Model\Service\Database\MySqlMap;
use TallTree\Roots\Install\Model\Service\File\FileMap;
use TallTree\Roots\Install\Factory;
use TallTree\Roots\Install\Repository;
use TallTree\Roots\Install\Installer;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../vendor/autoload.php';

$adapter = new Local(__DIR__ . '/../../');
$fileSystem = new Filesystem($adapter, [
    'visibility' => AdapterInterface::VISIBILITY_PRIVATE
]);

$configurationFile = $fileSystem->read('testing/Dev/conf.json');
$configuration = json_decode($configurationFile, true);
//$configuration = Yaml::parse($configurationFile);

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
$dbMap = new MySqlMap($query, $configuration['namespaces']);
$fileMap = new FileMap($fileSystem, $dbDir);
$factory = new Factory();
$repository = new Repository($dbMap, $fileMap, $factory);
$worker = new Installer(
    $repository,
    $query,
    $fileHandle,
    $dbMap,
    $fileMap,
    $factory,
    $configuration['namespaces']
);

$worker->installAll();

