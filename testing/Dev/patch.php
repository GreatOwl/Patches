#!/usr/bin/env php
<?php

use GreatOwl\Patches\Service\Database\Connection;
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
    $database['type'],
    $database['server'],
    $database['username'],
    $database['password'],
    $database['name']
);

$query = new \GreatOwl\Patches\Service\Database\Query($connection);
$fileHandle = new \GreatOwl\Patches\Service\File\Handle($fileSystem, $dbDir);
$dbMap = new \GreatOwl\Patches\Patch\Model\Service\Database\MySqlMap($query);
$fileMap = new \GreatOwl\Patches\Patch\Model\Service\File\FileMap($fileSystem, $dbDir);
$factory = new \GreatOwl\Patches\Patch\Factory();
$repository = new \GreatOwl\Patches\Patch\Repository($dbMap, $fileMap, $factory);
$worker = new \GreatOwl\Patches\Patch\Controller($repository, $query, $fileHandle, $dbMap, $fileMap);

$worker->patchAll();

