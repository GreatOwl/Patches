#!/usr/bin/env php
<?php

use TallTree\Roots\Service\Database\Connection;
use TallTree\Roots\Service\Database\PdoFactory;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;
use TallTree\Roots\Patch\Model\Service\Database\MySqlMap;
use TallTree\Roots\Patch\Model\Service\File\FileMap;
use TallTree\Roots\Patch\Factory;
use TallTree\Roots\Patch\Repository;
use TallTree\Roots\Patch\Patcher;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../vendor/autoload.php';

$adapter = new Local(__DIR__ . '/../../');
$fileSystem = new Filesystem($adapter, [
    'visibility' => AdapterInterface::VISIBILITY_PRIVATE
]);

$configurationFile = $fileSystem->read('testing/Dev/conf.yaml');
$configuration = Yaml::parse($configurationFile);

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
$dbMap = new MySqlMap($query);
$fileMap = new FileMap($fileSystem, $dbDir);
$factory = new Factory();
$repository = new Repository($dbMap, $fileMap, $factory);
$worker = new Patcher($repository, $query, $fileHandle, $dbMap, $fileMap);

$worker->patchAll();

