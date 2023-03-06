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

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class Customer implements BuilderInterface
{
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

        $address = $order->getBillingAddress()->getStreetLine1();
        if ($order->getBillingAddress()->getStreetLine2()) {
            $address .= ', '.$order->getBillingAddress()->getStreetLine2();
        }

        return [
            'customer' => [
                'first_name' => $order->getBillingAddress()->getFirstname(),
                'last_name' => $order->getBillingAddress()->getLastname(),
                'email' => $order->getBillingAddress()->getEmail(),
                'phone' => $order->getBillingAddress()->getTelephone(),
                'address' => $address ?: "",
                'city' => $order->getBillingAddress()->getCity() ?: "",
                'post_code' => $order->getBillingAddress()->getPostcode() ?: ""
            ]
        ];
    }
}
