<?php

namespace Esto\HirePurchase\Model\Checkout\Plugin;

use Esto\HirePurchase\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
class AgreementsValidator
{
    /**
     * @var ConfigProvider $configProvider
     */
    public $configProvider;

    /**
     * @var Session
     */
    public $_checkoutSession;

    /**
     * @var RestRequest
     */
    protected $request;

    /**
     * @param ConfigProvider $configProvider
     * @param Session $_checkoutSession
     * @param RestRequest $request
     */
    public function __construct(
        ConfigProvider $configProvider,
        Session $_checkoutSession,
        RestRequest $request
    ){
        $this->configProvider = $configProvider;
        $this->_checkoutSession = $_checkoutSession;
        $this->request = $request;
    }

    /**
     * @param $agreements
     * @param $result
     * @return boolean
     */
    public function afterIsValid($agreements, $result)
    {
        $paymentMethod = $this->getPaymentMethod();

        if(in_array($paymentMethod, ConfigProvider::METHODS_LIST)
            && $this->configProvider->getEnableTerms($paymentMethod)) {
            return true;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getPaymentMethod()
    {
        $paymentMethod = $this->_checkoutSession->getQuote()->getPayment()->getMethod();

        if (!$paymentMethod) {
            $result = $this->request->getRequestData();
            $paymentMethod = $result['paymentMethod']['method'] ?? null;
        }
        return $paymentMethod;
    }

}
