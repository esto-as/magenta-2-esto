<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Api;

use Magento\Framework\Exception\NoSuchEntityException;

interface BankRepositoryInterface
{
    /**
     * Save Bank
     *
     * @param \Esto\HirePurchase\Api\Data\BankInterface $bank
     *
     * @return \Esto\HirePurchase\Api\Data\BankInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\BankInterface $bank);

    /**
     * Get list
     *
     * @return \Esto\HirePurchase\Model\ResourceModel\Bank\Collection
     */
    public function getList();

    /**
     * Retrieve Bank
     *
     * @param string $bankName
     * @param string $countryCode
     *
     * @return \Esto\HirePurchase\Api\Data\BankInterface
     * @throws NoSuchEntityException
     */
    public function getBankByNameAndCountryCode(string $bankName, string $countryCode);
}
