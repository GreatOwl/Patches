<?php

use GreatOwl\Patches\Models\Service\Database\Connection;
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

$query = new \GreatOwl\Patches\Models\Service\Database\Query($connection);
$map = new \GreatOwl\Patches\Models\Patch\Map($query, $fileSystem, $dbDir);
$factory = new \GreatOwl\Patches\Models\Patch\Factory();
$repository = new \GreatOwl\Patches\Models\Patch\Repository($map, $factory);
$worker = new \GreatOwl\Patches\Worker($repository, $query, $map);

var_dump($worker->patchAll());

