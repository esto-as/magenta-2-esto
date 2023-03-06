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

namespace Esto\HirePurchase\Gateway\Validator\Response;

use Esto\HirePurchase\Helper\Data as EstoHelper;
use Esto\HirePurchase\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Esto\HirePurchase\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ResponseValidator extends AbstractValidator
{
    /**
     * @var EstoHelper
     */
    protected $helper;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param EstoHelper $helper
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        EstoHelper $helper
    ) {
        parent::__construct($resultFactory);
        $this->helper = $helper;
    }

    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);
        $isValid = true;
        $fails = [];

        if (!isset($response['data']) || !isset($response['mac'])
        ) {
            $isValid = false;
            $fails[] = __('Wrong response from the payment gateway. Try again, please, or contact support.');
        }

        if ($isValid) {
            // Validate mac string
            $configKeyPath = TransferFactory::CONFIG_AUTH_PASSWORD;

            if (in_array(
                $paymentDO->getPayment()->getMethod(),
                array(ConfigProvider::CODE,
                    ConfigProvider::CODE_PAY_LATER,
                    ConfigProvider::CODE_X,
                    ConfigProvider::CODE_PAY,
                    ConfigProvider::CODE_PAY_CARD
                )
            )){
                $customCountry = 0;
                $countryCode = $paymentDO->getPayment()->getOrder()->getBillingAddress()->getCountryId();
                if (isset(\Esto\HirePurchase\Gateway\Http\TransferFactory::CONFIG_CUSTOM[$countryCode])) {
                    $configPath = \Esto\HirePurchase\Gateway\Http\TransferFactory::CONFIG_CUSTOM[$countryCode]['config'];
                    $customCountry = $paymentDO->getPayment()->getMethodInstance()->getConfigData($configPath);
                }

                if (!$customCountry && $paymentDO->getPayment()->getAdditionalInformation('payment_method_key')) {
                    $countryCode = $this->helper->getCountry($paymentDO->getPayment()->getAdditionalInformation('payment_method_key'));
                    $configPath = \Esto\HirePurchase\Gateway\Http\TransferFactory::CONFIG_CUSTOM[$countryCode]['config'];
                    $customCountry = $paymentDO->getPayment()->getMethodInstance()->getConfigData($configPath);
                }

                if ($customCountry) {
                    $configKeyPath = TransferFactory::CONFIG_CUSTOM[$countryCode]['secret_key'];
                }
            }

            $apiKey = $paymentDO->getPayment()->getMethodInstance()->getConfigData($configKeyPath);
            $macString = strtoupper(hash('sha512', $response['data'].$apiKey));
            if ($macString !== $response['mac']) {
                $isValid = false;
                $fails[] = __('Wrong MAC value. Try again, please, or contact support.');
            }
        }

        if ($isValid) {
            $data = json_decode($response['data'], true);
            if (!is_array($data) || !isset($data['status']) || $data['status'] != 'CREATED') {
                $isValid = false;
                $fails[] = __('An error occurred on the payment gateway side while processing your request. Try again, please, or contact support.');
            }
        }

        return $this->createResult($isValid, $fails);
    }
}
