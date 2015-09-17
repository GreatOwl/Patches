<?php
namespace TallTree\Roots\Tools;

trait NameSpaceLoader
{

    protected $rootNamespace;
    protected $appNamespace;

    protected function loadNameSpaces(array $namespaces)
    {
        if (array_key_exists('root', $namespaces)) {
            $this->rootNamespace = $namespaces['root'] . "_";
        } else {
            $this->rootNamespace = "root_";
        }

        if (array_key_exists('app', $namespaces)) {
            $this->appNamespace = $namespaces['app'] . "_";
        } else {
            $this->appNamespace = "";
        }
    }
}
