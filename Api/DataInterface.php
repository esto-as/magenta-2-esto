<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Api;

interface DataInterface
{
    /**
     * Init Origin data
     *
     * @return void
     */
    public function initOrigData();

    /**
     * @return bool
     */
    public function hasDataChanges(): bool;

    /**
     * Get original data
     *
     * @return array|null
     */
    public function getOrigData();
}
