<?php
namespace TallTree\Roots\Install\Model\Service;

use TallTree\Roots\Install\Model\Install;

interface Map
{

    /**
     * @param string $table
     * @return array
     */
    public function getInstall($table);

    /**
     * @param Install $install
     * @return void
     */
    public function applyInstall(Install $install);

    /**
     * @param Install $originalInstall
     * @param Install $newInstall
     * @return void
     */
    public function updateInstall(Install $originalInstall, Install $newInstall);
}
