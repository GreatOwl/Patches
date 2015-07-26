#!/usr/bin/env php
<?php

use TallTree\Roots\Service\Database\Connection;
use TallTree\Roots\Service\Database\PdoFactory;
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

$query = new \TallTree\Roots\Service\Database\Query($connection);
$fileHandle = new \TallTree\Roots\Service\File\Handle($fileSystem, $dbDir);
$dbMap = new \TallTree\Roots\Patch\Model\Service\Database\MySqlMap($query);
$fileMap = new \TallTree\Roots\Patch\Model\Service\File\FileMap($fileSystem, $dbDir);
$factory = new \TallTree\Roots\Patch\Factory();
$repository = new \TallTree\Roots\Patch\Repository($dbMap, $fileMap, $factory);
$worker = new \TallTree\Roots\Patch\Controller($repository, $query, $fileHandle, $dbMap, $fileMap);

$worker->patchAll();

