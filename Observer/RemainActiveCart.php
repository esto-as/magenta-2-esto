<?php
namespace Esto\HirePurchase\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemainActiveCart implements ObserverInterface
{
    /**
     * We save cart even after it converts to order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        $estoMethods = ['esto_hirepurchase', 'esto_pay_later', 'esto_x', 'esto_pay', 'esto_pay_card'];
        $paymentMethod = $quote->getPayment()->getMethodInstance()->getCode();
        if (in_array($paymentMethod, $estoMethods)) {
            $quote->setIsActive(true)->save();
        }
    }
}
