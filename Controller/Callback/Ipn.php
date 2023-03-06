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

namespace Esto\HirePurchase\Controller\Callback;

use Esto\HirePurchase\Logger\Logger;
use Esto\HirePurchase\Helper\Data as EstoHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Esto\HirePurchase\Model\Ui\ConfigProvider as EstoConfigProvider;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Ipn extends Action implements CsrfAwareActionInterface
{
    const ERROR_CODE_CONTRACT_REJECTED = 1;

    const CONFIG_PATH_AUTO_INVOICE = 'payment/' . EstoConfigProvider::CODE . '/automatic_invoice';

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var EstoHelper
     */
    protected $helper;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    protected $allowedOrderStatuses = ['pending', 'pending_payment'];

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutHelper $checkoutHelper
     * @param OrderSender $orderSender
     * @param EstoHelper $helper
     * @param InvoiceSender $invoiceSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        CheckoutHelper $checkoutHelper,
        OrderSender $orderSender,
        EstoHelper $helper,
        InvoiceSender $invoiceSender,
        Logger $logger,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
        $this->orderSender = $orderSender;
        $this->helper = $helper;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Bypass CSRF since we have our own check
     *
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Bypass CSRF since we have our own check
     *
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Process requests from the Esto and from redirected customers.
     *
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $estoRequest = null;
        $isSuccess = true;

        if ($this->getRequest()->isPost()) {
            // Process POST request from the Esto system
            $estoRequest = @file_get_contents('php://input');
            if ($estoRequest) {
                @parse_str($estoRequest, $estoRequest);
                if (is_array($estoRequest) && isset($estoRequest['json'])) {
                    $estoRequest['json'] = @json_decode($estoRequest['json'], true);
                }
            }
        } else {
            // Process GET request for a redirected customer from the Esto site
            if ($this->getRequest()->getParam('json') && $this->getRequest()->getParam('mac')) {
                $estoRequest = [
                    'json' => @json_decode($this->getRequest()->getParam('json'), true),
                    'mac' => $this->getRequest()->getParam('mac')
                ];
            }
        }

        try {
            $this->logger->info(json_encode($estoRequest));
            if (is_array($estoRequest) && isset($estoRequest['json']) && isset($estoRequest['mac'])) {
                if (!is_array($estoRequest['json'])) {
                    throw new \Exception;
                }

                $order = $this->orderFactory->create()->loadByIncrementId($estoRequest['json']['reference']);

                $customCountrySelector = "";
                if(in_array(
                    $order->getPayment()->getData('method'),
                    array(EstoConfigProvider::CODE,
                        EstoConfigProvider::CODE_PAY_LATER,
                        EstoConfigProvider::CODE_X,
                        EstoConfigProvider::CODE_PAY,
                        EstoConfigProvider::CODE_PAY_CARD
                    )
                )){
                    $customCountrySelector = null;
                    if (isset(\Esto\HirePurchase\Gateway\Http\TransferFactory::CONFIG_CUSTOM[$order->getBillingAddress()->getCountryId()])) {
                        $configPath = \Esto\HirePurchase\Gateway\Http\TransferFactory::CONFIG_CUSTOM[$order->getBillingAddress()->getCountryId()]['config'];
                        $customCountry = $this->scopeConfig->getValue(
                            'payment/' . EstoConfigProvider::CODE . '/' . $configPath,
                            ScopeInterface::SCOPE_WEBSITE
                        );
                        if ($customCountry) $customCountrySelector = $order->getBillingAddress()->getCountryId();
                    }
                    if (!$customCountrySelector){
                        if (isset($order->getPayment()->getData()['additional_information'])) {
                            $paymentData = $order->getPayment()->getData()['additional_information'];
                            if (isset($paymentData['payment_method_key'])) {
                                $customCountry = $this->helper->getCountry($paymentData['payment_method_key']);
                                if ($customCountry) $customCountrySelector = $customCountry;
                            }
                        }
                    }
                }

                // Validate Esto request
                $this->helper->validateMac($estoRequest, $customCountrySelector);

                if (!$order->getId()) {
                    throw new \Exception;
                }

                if ($estoRequest['json']['status'] == 'REJECTED') {
                    throw new \Exception("Esto processing error: the contract was rejected.", self::ERROR_CODE_CONTRACT_REJECTED);
                }

                if (in_array($order->getStatus(), $this->allowedOrderStatuses)) {
                    try {
                        if ($this->scopeConfig->getValue(
                                self::CONFIG_PATH_AUTO_INVOICE,
                                ScopeInterface::SCOPE_WEBSITE
                            ) && $order->canInvoice()
                        ) {
                            $invoice = $order->prepareInvoice();
                            $invoice->register()->getResource()->save($invoice);
                            if (!$invoice->getEmailSent()) {
                                $this->invoiceSender->send($invoice);
                            }
                        }
                        $order->setState(Order::STATE_PROCESSING)
                            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
                            ->getResource()->save($order);

                        $quote = $this->quoteRepository->get($order->getQuoteId());
                        $quote->setIsActive(false)->save();

                        // Send order confirmation email to a customer -- REMOVED, now we send ot after order submitting
//                        if (!$order->getEmailSent()) {
//                            try {
//                                $this->orderSender->send($order, true);
//                            } catch (\Exception $e) {
//                            }
//                        }
                    } catch (\Exception $e) {
                        throw new \Exception('Esto processing error: '.$e->getMessage());
                    }
                }
            } else {
                throw new \Exception;
            }
        } catch (\Exception $e) {
            $isSuccess = false;
            if (isset($order) && $order->getId()) {
                if (in_array($order->getStatus(), $this->allowedOrderStatuses)
                    && $e->getCode() === self::ERROR_CODE_CONTRACT_REJECTED) {
                    try {
                        // Cancel current order
                        $order->cancel();
                        if ($this->getRequest()->isGet()) {
                            $this->checkoutHelper->getCheckout()->restoreQuote();
                        }
                    } catch (\Exception $e) {}
                }

                try {
                    $order->addStatusHistoryComment($e->getMessage());
                    $order->getResource()->save($order);
                } catch (\Exception $e) {}
            }
        } finally {
            if ($this->getRequest()->isGet()) {
                if (!$isSuccess) {
                    return $this->_redirect('checkout/cart');
                } else {
                    $customUrlsEnabled = $this->scopeConfig->getValue(
                        'payment/esto_hirepurchase/override_url',
                        ScopeInterface::SCOPE_WEBSITE
                    );
                    $customSuccessUrl = "";
                    if ($customUrlsEnabled) {
                        $customSuccessUrl = $this->scopeConfig->getValue(
                            'payment/esto_hirepurchase/override_success',
                            ScopeInterface::SCOPE_WEBSITE
                        );
                    }
                    return $customSuccessUrl ? $this->_redirect($customSuccessUrl) : $this->_redirect('checkout/onepage/success');
                }
            }
        }
    }
}
