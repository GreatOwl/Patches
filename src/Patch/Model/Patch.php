<?php
namespace GreatOwl\Patches\Patch\Model;

use GreatOwl\Patches\Tools\Container;

class Patch
{
    /**
     * @var Container $parameters
     */
    private $parameters;

    /**
     * @param array $raw
     */
    public function __construct($raw = [])
    {
        $this->validateParameters($raw, ['table','patch', 'query', 'rollback']);
        $this->parameters = new Container($raw);
    }

    public function getId($default = null)
    {
        return $this->parameters->get('id', $default);
    }

    public function getTable()
    {
        return $this->parameters->get('table');
    }

    public function getPatch()
    {
        return $this->parameters->get('patch');
    }

    public function getQuery()
    {
        return $this->parameters->get('query');
    }

    public function getRollback($default = '', $default)
    {
        return $this->parameters->get('rollback', $default);
    }

    public function dump()
    {
        return $this->parameters->dump();
    }

    private function validateParameters(array $raw, array $requiredParams = [])
    {
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $raw)) {
                throw new \InvalidArgumentException($param . ' is a required parameter.');
            }
        }
    }

}
