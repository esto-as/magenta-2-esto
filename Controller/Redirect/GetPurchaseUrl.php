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

namespace Esto\HirePurchase\Controller\Redirect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Sales\Model\Order;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class GetPurchaseUrl extends Action
{
    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @param Context        $context
     * @param CheckoutHelper $checkoutHelper
     * @param OrderSender $orderSender
     */
    public function __construct(
        Context $context,
        CheckoutHelper $checkoutHelper,
        OrderSender $orderSender
    )
    {
        parent::__construct($context);
        $this->checkoutHelper = $checkoutHelper;
        $this->orderSender = $orderSender;
    }

    /**
     * Return ESTO purchase URL from order payment object.
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isGet()) {
            return $this->_redirect('');
        }

        $lastOrder = $this->checkoutHelper->getCheckout()->getLastRealOrder();
        $responseData = ['success' => true];

        if (!$lastOrder instanceof Order || !$lastOrder->getId()) {
            $responseData['success'] = false;
            $responseData['errorMessage'] = __("The requested order can't be retrieved.");
        } elseif (!$lastOrder->getPayment()->getAdditionalInformation('purchase_url')) {
            $responseData['success'] = false;
            $responseData['errorMessage'] = __("Esto payment flow URL can't be retrieved.");
        } else {
            $responseData['url'] = $lastOrder->getPayment()->getAdditionalInformation('purchase_url');

            //send order email
            $this->orderSender->send($lastOrder, true);
        }

        if (!$responseData['success']) {
            if ($lastOrder instanceof Order && $lastOrder->getId() && $lastOrder->canCancel()) {
                $lastOrder->cancel();
                $lastOrder->getResource()->save($lastOrder);
                $this->checkoutHelper->getCheckout()->restoreQuote();
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData($responseData);
    }
}
