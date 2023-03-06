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

namespace Esto\HirePurchase\Gateway\Request\Builder;

use Esto\HirePurchase\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class Config implements BuilderInterface
{

    const SCHEDULE_TYPE_PAY_LATER = 'PAY_LATER';
    const SCHEDULE_TYPE_ESTO_X = 'ESTO_X';
    const SCHEDULE_TYPE_ESTO_PAY = 'ESTO_PAY';
    const SCHEDULE_TYPE_ESTO_PAY_CARD = 'ESTO_PAY';

    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();

        $result = [
            'reference' => $order->getOrderIncrementId(),
            'return_url' => $this->urlBuilder->getUrl('esto/callback/ipn'),
            'notification_url' => $this->urlBuilder->getUrl('esto/callback/ipn'),
            'cancel_url' => $this->urlBuilder->getUrl('esto/callback/cancel'),
            'connection_mode' => $payment->getPayment()->getMethodInstance()->getConfigData('mode'),
            'store_id' => $order->getStoreId()
        ];

        if($payment->getPayment()->getMethod() == ConfigProvider::CODE_PAY_LATER) {
            $result['schedule_type'] = self::SCHEDULE_TYPE_PAY_LATER;
        }

        if($payment->getPayment()->getMethod() == ConfigProvider::CODE_X) {
            $result['schedule_type'] = self::SCHEDULE_TYPE_ESTO_X;
        }

        if($payment->getPayment()->getMethod() == ConfigProvider::CODE_PAY) {
            $result['schedule_type'] = self::SCHEDULE_TYPE_ESTO_PAY;
            if (isset($payment->getPayment()->getAdditionalInformation()['payment_method_key'])) {
                $result['payment_method_key'] = $payment->getPayment()->getAdditionalInformation()['payment_method_key'];
            }
        }

        if($payment->getPayment()->getMethod() == ConfigProvider::CODE_PAY_CARD) {
            $result['schedule_type'] = self::SCHEDULE_TYPE_ESTO_PAY_CARD;
            if (isset($payment->getPayment()->getData()["method"])) {
                if ($payment->getPayment()->getData()["method"] == "esto_pay_card") {
                    $result['payment_method_key'] = "STRIPE_CARD";
                }
            }
        }
      
        if (in_array(
            $payment->getPayment()->getMethod(),
            array(ConfigProvider::CODE,
                ConfigProvider::CODE_PAY_LATER,
                ConfigProvider::CODE_X,
                ConfigProvider::CODE_PAY,
                ConfigProvider::CODE_PAY_CARD
            )
        )) {
            $result['custom_country'] = $order->getBillingAddress()->getCountryId();
        }

        return $result;
    }
}
