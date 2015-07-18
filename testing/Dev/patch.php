<?php

use GreatOwl\Patches\Connect;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../../vendor/autoload.php';

$adapter = new Local(__DIR__);
$fileSystem = new Filesystem($adapter, [
    'visibility' => AdapterInterface::VISIBILITY_PRIVATE
]);

$configurationFile = $fileSystem->read('conf.yaml');
$configuration = Yaml::parse($configurationFile);

$database = $configuration['database'];
$directory = $configuration['directory'];

$connection = new Connect(
    $database['type'],
    $database['server'],
    $database['username'],
    $database['password'],
    $database['name']
);
$dbHandle = $connection->getConnection();

var_dump($fileSystem->listContents($directory));
