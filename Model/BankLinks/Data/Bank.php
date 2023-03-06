<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\BankLinks\Data;

use Esto\HirePurchase\Model\DataModel;
use Esto\HirePurchase\Api\Data\BankInterface;

/**
 * Bank model
 */
class Bank extends DataModel implements BankInterface
{
    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->_get(self::BANK_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($bankId): BankInterface
    {
        return $this->setData(self::BANK_ID, $bankId);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status): BankInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode(): string
    {
        return $this->_get(self::COUNTRY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCountryCode($countryCode): BankInterface
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * @inheritDoc
     */
    public function getBankName(): string
    {
        return $this->_get(self::BANK_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setBankName($bankName): BankInterface
    {
        return $this->setData(self::BANK_NAME, $bankName);
    }

    /**
     * @inheritDoc
     */
    public function getBankLogo(): string
    {
        return $this->_get(self::BANK_LOGO);
    }

    /**
     * @inheritDoc
     */
    public function setBankLogo($bankLogo): BankInterface
    {
        return $this->setData(self::BANK_LOGO, $bankLogo);
    }
}
