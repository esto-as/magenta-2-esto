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

namespace Esto\HirePurchase\Gateway\Http;

use Esto\HirePurchase\Helper\Data as EstoHelper;
use Esto\HirePurchase\Helper\ConfigProvider as ConfigProvider;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\ConfigInterface;

class TransferFactory implements TransferFactoryInterface
{
    const CONFIG_AUTH_USERNAME = 'shop_id';
    const CONFIG_AUTH_PASSWORD = 'secret_key';
    const CONFIG_CUSTOM = [
        "EE" => [
            'config' => 'estonian_custom',
            'shop_id' => 'estonian_shop_id',
            'secret_key' => 'estonian_secret_key'
        ],
        "LT" => [
            'config' => 'lithuanian_custom',
            'shop_id' => 'lithuanian_shop_id',
            'secret_key' => 'lithuanian_secret_key'
        ],
        "LV" => [
            'config' => 'latvian_custom',
            'shop_id' => 'latvian_shop_id',
            'secret_key' => 'latvian_secret_key'
        ]
    ];

    private $countries = [
        "EE" => "https://api.esto.ee/v2/purchase/redirect",
        "LT" => "https://api.estopay.lt/v2/purchase/redirect",
        "LV" => "https://api.esto.lv/v2/purchase/redirect"
    ];

    /**
     * @var EstoHelper
     */
    protected $helper;

    protected $configProvider;
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param TransferBuilder $transferBuilder
     * @param ConfigInterface $config
     * @param EstoHelper $helper
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        ConfigInterface $config,
        ConfigProvider $configProvider,
        EstoHelper $helper
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $request)
    {
        $storeId = isset($request['store_id']) ? $request['store_id'] : null;
        unset($request['store_id']);

        $estoUrl = $this->configProvider->getApiUrl();
        $estoUserName = $this->config->getValue(self::CONFIG_AUTH_USERNAME, $storeId);
        $estoPassword = $this->config->getValue(self::CONFIG_AUTH_PASSWORD, $storeId);

        $countryCode = null;

        if (isset($request['payment_method_key']) && $request['payment_method_key'] !== "esto_pay_card") {
            $customCountry = $this->helper->getCountry($request['payment_method_key']);
            if ($customCountry) $countryCode = $customCountry;
        }

        if (isset($request['custom_country']) && isset(self::CONFIG_CUSTOM[$request['custom_country']])) {
            if ($this->config->getValue(self::CONFIG_CUSTOM[$request['custom_country']]['config'], $storeId)) {
                $countryCode = $request['custom_country'];
            }
        }

        if ($countryCode && isset($this->countries[$countryCode])) {
            $configPath = self::CONFIG_CUSTOM[$countryCode]['config'];
            if ($this->config->getValue($configPath, $storeId)) {
                $estoUrl = $this->countries[$countryCode];
                $estoUserName = $this->config->getValue(self::CONFIG_CUSTOM[$countryCode]['shop_id'], $storeId);
                $estoPassword = $this->config->getValue(self::CONFIG_CUSTOM[$countryCode]['secret_key'], $storeId);
            }
        }

        unset($request['custom_country']);

        return $this->transferBuilder
            ->setBody($request)
            ->setAuthUsername($estoUserName)
            ->setAuthPassword($estoPassword)
            ->setUri($estoUrl)
            ->build();
    }
}
