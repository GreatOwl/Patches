<?php
namespace TallTree\Roots\Service\Transform;


use TallTree\Roots\Service\Database\Connection;

class NameSpaces
{
    const REGEX_NS_FINDER = '^(?i)(?P<match>(?P<type>%s)(\s)(?P<container>[`\'"]|)(?P<table>(?P<space>\w+_)?(?P<name>\w+))(\g{container}))^';
    const REGEX_NS_REPLACEMENT = '/%s/';

    const QUERY_TABLE_STRUCTURE = '%1$s `%2$s`';
    const REGEX_TABLE_STRUCTURE = '/%s/';

    private $type;

    private $rootsNamespace;
    private $appNamespace;

    private $replacementType = [
        'mysql' => ['\sINTO', 'UPDATE', '\sFROM', '\sTABLE', '\sJOIN']
    ];

    public function __construct(Connection $connection, $namespaces = [])
    {
        $this->type = strtolower($connection->getType());
        $this->loadNameSpaces($namespaces);
    }

    public function getAppNameSpace()
    {
        return $this->appNamespace;
    }

    public function getRootsNameSpace()
    {
        return $this->rootsNamespace;
    }

    public function removeNameSpaceFromQuery($query)
    {
        $final = $this->updateQueryNameSpacing($query);
        return $final;
    }

    public function addNameSpaceToQuery($query, $app = true)
    {
        $final = $this->updateQueryNameSpacing($query, $app);
        return $final;
    }

    private function updateQueryNameSpacing($query, $app = null)
    {
        $matches = [];
        $replaces = [];
        $replacements = [];
        $regex = $this->loadRegexFinder();
        preg_match_all($regex, $query, $matches, PREG_SET_ORDER );

        foreach ($matches as $match) {
            if ($app === true) {
                $namespace = $this->getAppNamespace();
            } elseif ($app === false) {
                $namespace = $this->getRootsNamespace();
            } else {
                $namespace = '';
            }
            list($replaces[], $replacements[]) = $this->reWriteElement($match, $namespace);
        }

        return preg_replace($replaces, $replacements,$query);
    }

    private function reWriteElement($match, $namespace = '')
    {
        $type = $match['type'];
        $table = $namespace . $match['name'];
        $replaces = sprintf(self::REGEX_TABLE_STRUCTURE, $match['match']);
        $replacements = sprintf(self::QUERY_TABLE_STRUCTURE, $type, $table);
        return [$replaces, $replacements];
    }

    private function loadRegexFinder()
    {
        $types = [];

        if (array_key_exists($this->type, $this->replacementType)) {
            $types = $this->replacementType[$this->type];
        }

        return sprintf(self::REGEX_NS_FINDER, implode('|', $types));
    }

    protected function loadNameSpaces(array $namespaces)
    {
        if (array_key_exists('root', $namespaces)) {
            $this->rootsNamespace = $namespaces['root'] . "_";
        } else {
            $this->rootsNamespace = "roots_";
        }

        if (array_key_exists('app', $namespaces)) {
            $this->appNamespace = $namespaces['app'] . "_";
        } else {
            $this->appNamespace = "";
        }
    }

}
