<?php

namespace Esto\HirePurchase\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Esto\HirePurchase\Model\Ui\ConfigProvider;

class PaymentMethodIsActive implements ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    )
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // get custom options value of cart items
        $method = $observer->getEvent()->getMethodInstance()->getCode();
        if ($this->validate($method)) {

            $enabledCountrys = $this->configProvider->getEnabledCountrys($method);
            $country = $observer->getEvent()->getQuote()->getBillingAddress()->getCountry();

            if (!in_array($country, $enabledCountrys)) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }
    }

    public function validate($method)
    {
        return in_array($method, ConfigProvider::METHODS_LIST) && $this->configProvider->isCountrysEnabled($method);
    }
}
