<?php
/**
 * @copyright ©2005—2015 Quicken Loans Inc. All rights reserved. Trade Secret, Confidential and Proprietary. Any
 *     dissemination outside of Quicken Loans is strictly prohibited.
 */

namespace TallTree\Roots\Patch;

use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Patch\Model\Collection;
use TallTree\Roots\Patch\Model\Patch;

class FilterFactory
{
    /**
     * Will filter out all patches that match between 2 collections.
     *
     * @param Collection $patched
     * @return \Closure
     */
    public function findUnmatched(Collection $patched)
    {
        return function(Patch $patch) use ($patched) {
            /** @var Patch $usedPatch */
            foreach ($patched as $usedPatch) {
                if ($patch->getPatch() === $usedPatch->getPatch() && $patch->getQuery() == $usedPatch->getQuery()) {
                    return false;
                }

                if ($patch->getQuery() === $usedPatch->getPatch()) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * Will return all patches that are greater than the applied install provided.
     *
     * @param Install $originalInstall
     * @return \Closure
     */
    public function findAfterInstall(Install $originalInstall)
    {
        return function(Patch $patch) use ($originalInstall) {
            if ($originalInstall->getPatch() == 0) {
                return $patch->getPatch() >= $originalInstall->getPatch();
            } else {
                return $patch->getPatch() > $originalInstall->getPatch();
            }
        };
    }

}
