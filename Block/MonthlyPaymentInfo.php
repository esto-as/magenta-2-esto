<?php

namespace Esto\HirePurchase\Block;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;
use Esto\HirePurchase\Model\Ui\ConfigProvider;
use Esto\HirePurchase\Model\OperatingCache;
use Magento\Framework\Serialize\SerializerInterface;

class MonthlyPaymentInfo extends Template
{
    const ESTO_CALCULATE_ENDPOINT = 'https://api.esto.ee/v2/calculate/payments';
    const DEFAULT_TITLE = 'Monthly payment from &euro;%summa%';
    const TITLE_PLACEHOLDER = '%summa%';
    const MONTHLY_PAYMENT_ESTO_FIELD = 'monthly_payment';
    const CONFIG_PATH_MONTHLY_PAYMENT_BLOCK_TEXT = 'payment/' . ConfigProvider::CODE . '/monthly_payment_block_title';
    const CONFIG_PATH_MIN_ORDER_TOTAL = 'payment/' . ConfigProvider::CODE . '/min_order_total';
    const CONFIG_PATH_LOGO = 'payment/' . ConfigProvider::CODE . '/monthly_payment_block_logo';
    const CONFIG_PATH_REDIRECT_URL = 'payment/' . ConfigProvider::CODE . '/monthly_payment_block_url';
    const CONFIG_PATH_BLOCK_ACTIVE = 'payment/' . ConfigProvider::CODE . '/monthly_payment_block_is_active';
    const CONFIG_SHOP_ID = 'payment/' . ConfigProvider::CODE . '/shop_id';

    /**
     * @var OperatingCache
     */
    private $operatingCache;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Template\Context $context
     * @param Registry $registry
     * @param ZendClientFactory $clientFactory
     * @param ConfigProvider $configProvider
     * @param OperatingCache $operatingCache
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context    $context,
        Registry            $registry,
        ZendClientFactory       $clientFactory,
        ConfigProvider      $configProvider,
        OperatingCache      $operatingCache,
        SerializerInterface $serializer,
        array               $data = []
    )
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->clientFactory = $clientFactory;
        $this->configProvider = $configProvider;
        $this->operatingCache = $operatingCache;
        $this->serializer = $serializer;
    }

    /**
     * Get a minimum monthly payment amount from the Esto system for provided total amount.
     *
     * @return float
     */
    public function getMinimumMonthlyAmount()
    {
        $resultAmount = 0;
        /** @var Product $product */
        $product = $this->registry->registry('current_product');

        if ($product) {
            $priceModel = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
            $productMinimalFinalPrice = $priceModel->getValue();
            $paymentMethodMinOrderTotal = (float)$this->_scopeConfig->getValue(
                self::CONFIG_PATH_MIN_ORDER_TOTAL,
                ScopeInterface::SCOPE_WEBSITE
            );

            $cache = $this->operatingCache->loadCache();
            $cached = $cache->checkPrice($productMinimalFinalPrice);

            if ($productMinimalFinalPrice && $productMinimalFinalPrice >= $paymentMethodMinOrderTotal) {
                $resultAmount = $this->loadAmout($productMinimalFinalPrice);
            }
            $resultAmount = $cached
                ?? $cache->getPrice($productMinimalFinalPrice)
                ?: $cache->setPrice($productMinimalFinalPrice, $resultAmount);
            $cache->saveCache();
        }

        return $resultAmount;
    }

    private function loadAmout($productPrice)
    {
        $client = $this->clientFactory->create();
        $client->setMethod(); // GET request
        $client->setUri(self::ESTO_CALCULATE_ENDPOINT);
        $client->setParameterGet(
            [
                'amount' => $productPrice,
                'shop_id' => $this->_scopeConfig->getValue(self::CONFIG_SHOP_ID, ScopeInterface::SCOPE_WEBSITE)
            ]
        );
        try {
            $response = $client->request();
            $estoPricing =$this->serializer->unserialize($response->getBody());
            if (is_array($estoPricing) && isset($estoPricing['monthly_payment'])) {
                $resultAmount = (float)$estoPricing['monthly_payment'];
            }
        } catch (\Exception $e) {
        }
        return $resultAmount;
    }

    /**
     * Get configured block title with applied minimum monthly payment amount.
     *
     * @param bool $formatAmount
     *
     * @return string
     */
    public function getTitle($formatAmount = true)
    {
        $blockText = $this->_scopeConfig
            ->getValue(self::CONFIG_PATH_MONTHLY_PAYMENT_BLOCK_TEXT, ScopeInterface::SCOPE_STORE);
        $isActive = $this->_scopeConfig
            ->isSetFlag(self::CONFIG_PATH_BLOCK_ACTIVE, ScopeInterface::SCOPE_STORE);
        if (!$isActive) {
            return '';
        }

        if (empty($blockText)) {
            $blockText = static::DEFAULT_TITLE;
        }

        if (strpos($blockText, static::TITLE_PLACEHOLDER) !== false) {
            $monthlyAmount = $this->getMinimumMonthlyAmount();
            if ($monthlyAmount === 0) {
                $blockText = '';
            } else {
                if ($formatAmount) {
                    $monthlyAmount = number_format($monthlyAmount, 2);
                }
                $blockText = str_replace(static::TITLE_PLACEHOLDER, $monthlyAmount, $blockText);
            }
        }

        return $blockText;
    }

    /**
     * Get the "Monthly Payment" block logo URL.
     *
     * @return string|null
     */
    public function getLogoUrl()
    {
        static $logoUrl = null;

        if (!$logoUrl && $this->_scopeConfig->getValue(self::CONFIG_PATH_LOGO, ScopeInterface::SCOPE_STORE)) {
            $logoUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                ConfigProvider::LOGO_MEDIA_PATH . '/' . $this->_scopeConfig->getValue(self::CONFIG_PATH_LOGO, ScopeInterface::SCOPE_STORE);
        } else {
            $logoUrl = $this->configProvider->getLogoUrl(ConfigProvider::CODE);
        }

        return $logoUrl;
    }

    /**
     * Get the "Monthly Payment" block redirect URL.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->_scopeConfig->getValue(self::CONFIG_PATH_REDIRECT_URL, ScopeInterface::SCOPE_WEBSITE);
    }
}
