<?php
namespace GreatOwl\Patches\Models\Patch;


use GreatOwl\Patches\Container;

class Patch
{
    /**
     * @var array $parameters
     */
    private $parameters;

    /**
     * @param array $raw
     */
    public function __construct($raw = [])
    {
        $this->validateParameters($raw, ['table','patch', 'query']);
        $this->parameters = new Container($raw);
    }

    public function getId($default = null)
    {
        return $this->parameters->get('id', $default);
    }

    public function getTable($default = null)
    {
        return $this->parameters->get('table', $default);
    }

    public function getPatch($default = null)
    {
        return $this->parameters->get('patch', $default);
    }

    public function getQuery($default = '')
    {
        return $this->parameters->get('query', $default);
    }

    public function getStatus($default = false)
    {
        return $this->parameters->get('status', $default);
    }

    public function setStatus($status)
    {
        $this->parameters->set('status', $status);
    }

    public function getRollback($default = '', $default)
    {
        return $this->parameters->get('rollback', $default);
    }

    public function getRollbackStatus($default = null)
    {
        return $this->parameters->get('rollback_status', $default);
    }

    public function setRollbackStatus($status)
    {
        $this->parameters->set('rollback_status', $status);
    }

    public function getChanges()
    {
        return $this->parameters->changes();
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
