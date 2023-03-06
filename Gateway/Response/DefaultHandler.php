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

namespace Esto\HirePurchase\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class DefaultHandler implements HandlerInterface
{
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $stateObject = SubjectReader::readStateObject($handlingSubject);
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        $data = json_decode($response['data'], true);

        $payment->setAdditionalInformation('id', $data['id'])
            ->setAdditionalInformation('purchase_url', $data['purchase_url'])
            ->setAdditionalInformation('connection_mode', $data['is_test'] ? 'test' : 'live')
            ->setAdditionalInformation('status', $data['status']);

        $orderStatus = $payment->getMethodInstance()
            ->getConfigData('order_status');
        if ($orderStatus == Order::STATE_PENDING_PAYMENT) {
            $stateObject->setStatus($orderStatus)->setState($orderStatus);
        }
        $payment->getOrder()->setCanSendNewEmailFlag(false);
    }
}
