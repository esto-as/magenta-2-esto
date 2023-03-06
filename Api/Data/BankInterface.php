<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Api\Data;

use Esto\HirePurchase\Api\DataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface BankInterface extends ExtensibleDataInterface, DataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BANK_ID = 'entity_id';
    const STATUS = 'status';
    const COUNTRY_CODE = 'country_code';
    const BANK_NAME = 'bank_name';
    const BANK_LOGO = 'bank_logo';

    /**
     * Table names
     */
    const ESTO_COUNTRY_BANKS_TABLE = 'esto_country_banks';

    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set ID
     *
     * @param int $bankId
     * @return BankInterface
     */
    public function setEntityId($bankId): BankInterface;

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Set status
     *
     * @param string $status
     * @return BankInterface
     */
    public function setStatus($status): BankInterface;

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * Set ountry code
     *
     * @param string $countryCode
     * @return BankInterface
     */
    public function setCountryCode($countryCode): BankInterface;

    /**
     * Get bank name
     *
     * @return string
     */
    public function getBankName(): string;

    /**
     * Set bank name
     *
     * @param string $bankName
     * @return BankInterface
     */
    public function setBankName($bankName): BankInterface;

    /**
     * Get bank logo
     *
     * @return string
     */
    public function getBankLogo(): string;

    /**
     * Set bank logo
     *
     * @param string $bankLogo
     * @return BankInterface
     */
    public function setBankLogo($bankLogo): BankInterface;
}
