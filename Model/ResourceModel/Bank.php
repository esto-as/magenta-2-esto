<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\ResourceModel;

use Esto\HirePurchase\Api\Data\BankInterface;

class Bank extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('esto_country_banks', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param $bankName
     * @param $countryCode
     * @return $this
     */
    public function loadByNameAndCountryCode(\Magento\Framework\Model\AbstractModel $object, $bankName, $countryCode)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(BankInterface::ESTO_COUNTRY_BANKS_TABLE))
            ->where('country_code = :country_code AND bank_name = :bank_name');

        $data = $this->getConnection()->fetchRow($select, [':country_code' => $countryCode, ':bank_name' => $bankName]);

        if ($data) {
            $data['entity_id'] = (int)$data['entity_id'];
            $object->setData($data);
        }

        return $this;
    }

    /**
     * @param $bankIds
     * @return $this
     */
    public function disableBanks($bankIds)
    {
        $this->getConnection()->update(
            $this->getTable(BankInterface::ESTO_COUNTRY_BANKS_TABLE),
            ['status' => 0],
            ['entity_id NOT IN (?)' => $bankIds]
        );

        return $this;
    }
}
