<?php
namespace TallTree\Roots\Install;

use TallTree\Roots\Install\Model\Install;

class Factory
{
    /**
     * @param array $raw
     * @return Install
     */
    public function createInstall(array $raw)
    {
        return new Install($raw);
    }
}
