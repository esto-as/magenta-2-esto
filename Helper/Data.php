<?php
/**
 * Zaproo Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Zaproo does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Zaproo does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zaproo
 * @package    Esto_HirePurchase
 * @version    1.0.2
 * @copyright  Copyright (c) Zaproo Co. (http://www.zaproo.com)
 */

namespace Esto\HirePurchase\Helper;

use Esto\HirePurchase\Gateway\Http\TransferFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Esto\HirePurchase\Model\Ui\ConfigProvider as EstoConfigProvider;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_SECRET_KEY = 'payment/'.EstoConfigProvider::CODE.'/secret_key';

    /**
     * Validate MAC of Esto request.
     *
     * @param array $estoRequest Decoded Esto request
     *
     * @throws \Exception
     */
    public function validateMac(array $estoRequest, string $customCountrySelector = null)
    {
        $configPath = self::CONFIG_PATH_SECRET_KEY;
        if ($customCountrySelector) {
            if ($this->scopeConfig->getValue('payment/'.EstoConfigProvider::CODE.'/'.TransferFactory::CONFIG_CUSTOM[$customCountrySelector]['config'])) {
                $configPath = 'payment/'.EstoConfigProvider::CODE.'/'.TransferFactory::CONFIG_CUSTOM[$customCountrySelector]['secret_key'];
            } else {
                $configPath = 'payment/'.EstoConfigProvider::CODE.'/secret_key';
            }
        }
        $apiKey = $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
        $macString = strtoupper(hash('sha512', @json_encode($estoRequest['json']).$apiKey));
        if ($macString !== $estoRequest['mac']) {
            throw new \Exception;
        }
    }

    public function getCountry(string $method)
    {
        if (strpos($method, "_EE")) {
            return "EE";
        } elseif (strpos($method, "_LT")) {
            return "LT";
        } elseif (strpos($method, "_LV")) {
            return "LV";
        } elseif (strpos($method, "_FI")) {
            return "EE";
        }
        return false;
    }
}
