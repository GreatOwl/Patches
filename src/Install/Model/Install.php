<?php
namespace TallTree\Roots\Install\Model;

use TallTree\Roots\Tools\Container;

class Install
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
        $this->validateParameters($raw, ['table','install']);
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

    public function getInstall()
    {
        return $this->parameters->get('install');
    }

    public function getPatche()
    {
        return $this->parameters->get('patch');
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
