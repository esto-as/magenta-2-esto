<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\ResourceModel\Bank;

/**
 * Collection for displaying grid
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Esto\HirePurchase\Model\BankLinks\Bank::class,
            \Esto\HirePurchase\Model\ResourceModel\Bank::class
        );
    }
}
