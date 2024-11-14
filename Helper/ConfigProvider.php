<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    const SPECIFIC_COUTRIES_PATH = 'payment/esto_pay/specific_countries';
    const ENDPOINT_COUNTRY_CONFIG = 'payment/esto_hirepurchase/request_endpoint';
    const API_ENDPOINTS = [
        "EE" => "https://api.esto.ee/v2/",
        "LT" => "https://api.estopay.lt/v2/",
        "LV" => "https://api.esto.lv/v2/"
    ];
    const DEFAULT_COUNTRY_CODE = 'EE';
    const PURCHASE_REDIRECT = 0;
    const PURCHASE_WITHOUT_REDIRECT = 1;
    const AVAILABLE_PAYMENT_METHODS = 2;

    const REQUEST_TYPES = [
        self::PURCHASE_REDIRECT => "purchase/redirect",
        self::PURCHASE_WITHOUT_REDIRECT => "purchase/local",
        self::AVAILABLE_PAYMENT_METHODS => "purchase/payment-methods",
    ];

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
    )
    {
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
    public function getApiUrl($type = self::PURCHASE_REDIRECT): string
    {
        $uri = $this->getApiUri();
        return $uri . self::REQUEST_TYPES[$type];
    }

    /**
     * @return string
     */
    public function getApiUri(): string
    {
        $countryCode = $this->scopeConfig->getValue(self::ENDPOINT_COUNTRY_CONFIG, ScopeInterface::SCOPE_STORE);
        if (!$countryCode) {
            return self::API_ENDPOINTS[self::DEFAULT_COUNTRY_CODE];
        }
        return self::API_ENDPOINTS[$countryCode];
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
