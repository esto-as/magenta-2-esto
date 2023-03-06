<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\Ui;

use Esto\HirePurchase\Model\BankLinks\BankRepository;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Magento\Directory\Model\CountryFactory;
use Esto\HirePurchase\Model\ResourceModel\Bank\Collection;
use Magento\Framework\Locale\Resolver;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'esto_hirepurchase';
    const CODE_PAY_LATER = 'esto_pay_later';
    const CODE_X = 'esto_x';
    const CODE_PAY = 'esto_pay';
    const CODE_PAY_CARD = 'esto_pay_card';

    const LOGO_MEDIA_PATH = 'payment/logo';
    const SPECIFIC_COUTRIES_PATH = 'payment/esto_pay/specific_countries';
    const DEFAULT_LOGOS = [
        self::CODE => 'ESTO_HIRE PURCHASE_EST.svg',
        self::CODE_PAY_LATER => 'ESTO_PAY LATER_ENG.svg',
        self::CODE_X => 'ESTO_X_ENG.svg',
        self::CODE_PAY => 'ESTO_PAY.svg',
        self::CODE_PAY_CARD => 'cardpayments.svg'
    ];
    const STORE_SPECIFIC = [
        self::CODE => 'ESTO_HIRE PURCHASE_',
        self::CODE_PAY_LATER => 'ESTO_PAY LATER_',
        self::CODE_X => 'ESTO_X_'
    ];
    const SPECIFIC_LOCALES = [
        'EN' => 'ENG',
        'ET' => 'EST',
        'LT' => 'LT',
        'LV' => 'LV',
        'RU' => 'RU'
    ];

    const METHODS_LIST = [
        self::CODE,
        self::CODE_PAY_LATER,
        self::CODE_X,
        self::CODE_PAY,
        self::CODE_PAY_CARD
    ];

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Template
     */
    private $baseBlock;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Esto\HirePurchase\Model\BankLinks\BankRepository
     */
    private $bankRepository;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * ConfigProvider constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Esto\HirePurchase\Model\BankLinks\BankRepository $bankRepository
     * @param \Magento\Framework\View\Element\Template $baseBlock
     * @param Resolver $localeResolver
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CountryFactory $countryFactory,
        BankRepository $bankRepository,
        Template $baseBlock,
        Resolver $localeResolver
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->baseBlock = $baseBlock;
        $this->countryFactory = $countryFactory;
        $this->bankRepository = $bankRepository;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'logo' => $this->getLogoUrl(self::CODE),
                    'description' => $this->getDescription(self::CODE),
                    'enable_terms' => $this->getEnableTerms(self::CODE),
                    'terms_text' => $this->getTermsText(self::CODE),
                    'terms_popup' => $this->getTermsPopup(self::CODE)
                ],
                self::CODE_X => [
                    'logo' => $this->getLogoUrl(self::CODE_X),
                    'description' => $this->getDescription(self::CODE_X),
                    'enable_terms' => $this->getEnableTerms(self::CODE_X),
                    'terms_text' => $this->getTermsText(self::CODE_X),
                    'terms_popup' => $this->getTermsPopup(self::CODE_X),
                    'calculator' => $this->getEstoXCalculatorEnabled()
                ],
                self::CODE_PAY_LATER => [
                    'logo' => $this->getLogoUrl(self::CODE_PAY_LATER),
                    'description' => $this->getDescription(self::CODE_PAY_LATER),
                    'enable_terms' => $this->getEnableTerms(self::CODE_PAY_LATER),
                    'terms_text' => $this->getTermsText(self::CODE_PAY_LATER),
                    'terms_popup' => $this->getTermsPopup(self::CODE_PAY_LATER)
                ],
                self::CODE_PAY => [
                    'logo' => $this->getLogoUrl(self::CODE_PAY),
                    'description' => $this->getDescription(self::CODE_PAY),
                    'enable_terms' => $this->getEnableTerms(self::CODE_PAY),
                    'terms_text' => $this->getTermsText(self::CODE_PAY),
                    'terms_popup' => $this->getTermsPopup(self::CODE_PAY),
                    'show_banks' => $this->getShowBanks(self::CODE_PAY),
                    'banks_columns' => $this->getBanksColumns(self::CODE_PAY),
                    'countries' => $this->getSpecificCountries(),
                    'banks' => $this->getBanks(),
                ],
                self::CODE_PAY_CARD => [
                    'logo' => $this->getEstoCardLogo(),
                    'description' => $this->getDescription(self::CODE_PAY_CARD),
                    'enable_terms' => $this->getEnableTerms(self::CODE_PAY_CARD),
                    'terms_text' => $this->getTermsText(self::CODE_PAY_CARD),
                    'terms_popup' => $this->getTermsPopup(self::CODE_PAY_CARD)
                ]
            ],
            'locale' => $this->countryFactory->create()->loadByCode(substr($this->localeResolver->getLocale(),0,2))->getName()
        ];
    }

    /**
     * @return array
     */
    public function getSpecificCountries(): array
    {
        $countries = $this->scopeConfig->getValue(self::SPECIFIC_COUTRIES_PATH, ScopeInterface::SCOPE_STORE);
        $countries = explode(',', $countries);
        $countryNames = [];
        foreach ($countries as $countryCode){
            $countryNames[] = $this->countryFactory->create()->loadByCode($countryCode)->getName();
        }

        return $countryNames;
    }

    /**
     * @return string
     */
    public function getEstoCardLogo(): string
    {
        $logoUrl = $this->getLogoUrl(self::CODE_PAY_CARD);

        if (!$logoUrl) {
            /** @var Collection $bankCollection */
            $bankCollection = $this->bankRepository->getList();
            $bankCollection->addFieldToFilter('status', '1')->addFieldToFilter('bank_name', 'STRIPE_CARD');
            foreach ($bankCollection as $bank){
                if ($bank->getBankLogo()) {
                    $logoUrl = $bank->getBankLogo();
                    break;
                }
            }
        }

        return $logoUrl;
    }

    /**
     * @return array
     */
    public function getBanks(): array
    {
        /** @var Collection $bankCollection */
        $bankCollection = $this->bankRepository->getList();
        $countryCodes = explode(',', $this->scopeConfig->getValue(self::SPECIFIC_COUTRIES_PATH, ScopeInterface::SCOPE_STORE));
        $bankCollection
            ->addFieldToFilter('status', '1')
            ->addFieldToFilter('country_code', ['in' => $countryCodes])
            ->addFieldToFilter('bank_name', ['neq' => 'STRIPE_CARD']);
        $banksData = [];
        foreach ($bankCollection as $bank){
            $countryName = $this->countryFactory->create()->loadByCode($bank->getCountryCode())->getName();
            $banksData[$countryName][] = ['name' => $bank->getBankName(), 'logo' => $bank->getBankLogo()];
        }

        return $banksData;
    }

    public function getDescription($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/description', ScopeInterface::SCOPE_STORE);
    }

    public function getLogoUrl($method)
    {
        $logoUrl = null;
        $logoMediaPath = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::LOGO_MEDIA_PATH .'/' . $method . '/';
        $configuredImage = $this->scopeConfig->getValue('payment/' . $method . '/logo', ScopeInterface::SCOPE_STORE);

        if ($configuredImage) {
            $logoUrl = $logoMediaPath . $configuredImage;
        } else {
            $currentLocaleCode = $this->localeResolver->getLocale();
            $languageCode = strtoupper(substr($currentLocaleCode, 0,2));
            if(isset(self::SPECIFIC_LOCALES[$languageCode])) {
                if (isset(self::STORE_SPECIFIC[$method])){
                    $logoUrl = $this->baseBlock->getViewFileUrl('Esto_HirePurchase::images/' . self::STORE_SPECIFIC[$method] . self::SPECIFIC_LOCALES[$languageCode] . '.svg');
                }
            }

            if (!$logoUrl) {
                $logoUrl = isset(self::DEFAULT_LOGOS[$method]) ? $this->baseBlock->getViewFileUrl('Esto_HirePurchase::images/' . self::DEFAULT_LOGOS[$method]) : "";
            }
        }

        return $logoUrl;
    }

    public function getEstoXCalculatorEnabled()
    {
        return $this->scopeConfig->getValue('payment/esto_x/calculator', ScopeInterface::SCOPE_STORE);
    }

    public function getTermsText($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/terms_text', ScopeInterface::SCOPE_STORE);
    }

    public function getEnableTerms($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/enable_terms', ScopeInterface::SCOPE_STORE);
    }

    public function getTermsPopup($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/terms_popup', ScopeInterface::SCOPE_STORE);
    }

    public function getShowBanks($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/show_banks', ScopeInterface::SCOPE_STORE);
    }

    public function getBanksColumns($method)
    {
        return $this->scopeConfig->getValue('payment/' . $method . '/banks_columns', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isCountrysEnabled($method)
    {
        return (bool)$this->scopeConfig->getValue('payment/' . $method . '/countries_status', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $method
     * @return array
     */
    public function getEnabledCountrys($method)
    {
        $config = $this->scopeConfig->getValue('payment/' . $method . '/countries', ScopeInterface::SCOPE_STORE);
        if ($config && !empty(trim($config))) {
            return explode(',', $config);
        }
        return [];
    }
}
