<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\BankLinks;

/**
 * Bank model
 */
class Bank extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Esto\HirePurchase\Model\ResourceModel\Bank::class);
    }
}
