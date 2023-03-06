<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    const SPECIFIC_COUTRIES_PATH = 'payment/esto_pay/specific_countries';
    const API_URL_PATH = 'payment/esto_pay/api_url';
    const MODE_PATH = 'payment/esto_hirepurchase/mode';
    const SHOP_ID_PATH = 'payment/esto_hirepurchase/shop_id';
    const API_KEY_PATH = 'payment/esto_hirepurchase/secret_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getSpecificCountries(): string
    {
        return $this->scopeConfig->getValue(self::SPECIFIC_COUTRIES_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->scopeConfig->getValue(self::API_URL_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->scopeConfig->getValue(self::MODE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->scopeConfig->getValue(self::SHOP_ID_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->scopeConfig->getValue(self::API_KEY_PATH, ScopeInterface::SCOPE_STORE);
    }
}
