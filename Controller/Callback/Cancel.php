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
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderFactory;
use Esto\HirePurchase\Helper\Data as EstoHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Cancel extends Action implements CsrfAwareActionInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EstoHelper
     */
    protected $helper;

    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context              $context
     * @param OrderFactory         $orderFactory
     * @param EstoHelper           $helper
     * @param CheckoutHelper       $checkoutHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        EstoHelper $helper,
        CheckoutHelper $checkoutHelper,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->checkoutHelper = $checkoutHelper;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
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
     * Process cancel request from the Esto.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $estoRequest = null;
        $isSuccess = true;

        if ($this->getRequest()->getParam('json') && $this->getRequest()->getParam('mac')) {
            $estoRequest = [
                'json' => $this->getRequest()->getParam('json'),
                'mac' => $this->getRequest()->getParam('mac')
            ];
        }

        try {
            $this->logger->info(json_encode($estoRequest));
            if (is_array($estoRequest) && isset($estoRequest['json']) && isset($estoRequest['mac'])) {
                // Validate Esto request
                $this->helper->validateMac($estoRequest);
                $estoRequest['json'] = @json_decode($estoRequest['json'], true);
                if (!is_array($estoRequest['json'])) {
                    throw new \Exception;
                }

                $order = $this->orderFactory->create()->loadByIncrementId($estoRequest['json']['reference']);

                if (!$order->getId()) {
                    throw new \Exception;
                }

                try {
                    $order->cancel()->getResource()->save($order);
                } catch (\Exception $e) {
                    throw new \Exception('Esto processing error: '.$e->getMessage());
                }
            } else {
                throw new \Exception;
            }
        } catch (\Exception $e) {
            $isSuccess = false;
            if (isset($order) && $order->getId()) {
                try {
                    $order->addStatusHistoryComment($e->getMessage());
                    $order->getResource()->save($order);
                } catch (\Exception $e) {}
            }
        } finally {
            if(!isset($order)) {
                if (is_array(@json_decode($estoRequest['json'], true))) {
                    $order = $this->orderFactory->create()->loadByIncrementId(@json_decode($estoRequest['json'], true)['reference']);
                }
            }
            $method = $order ? $order->getPayment()->getMethod() : 'esto_hirepurchase';
            $retain = $this->scopeConfig->getValue(
                'payment/' . $method . '/retain',
                ScopeInterface::SCOPE_STORE
            );
            if ($this->getRequest()->isGet() && $retain) {
                $this->checkoutHelper->getCheckout()->restoreQuote();
            }
            if (!$isSuccess) {
                return $this->_redirect('');
            } else {
                $customUrlsEnabled = $this->scopeConfig->getValue(
                    'payment/esto_hirepurchase/override_url',
                    ScopeInterface::SCOPE_WEBSITE
                );
                $customCancelUrl = "";
                if ($customUrlsEnabled) {
                    $customCancelUrl = $this->scopeConfig->getValue(
                        'payment/esto_hirepurchase/override_cancel',
                        ScopeInterface::SCOPE_WEBSITE
                    );
                }

                if ($customCancelUrl) {
                    return $this->_redirect($customCancelUrl);
                } else {
                    /** @var Page $resultPage */
                    $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                    $resultPage->getLayout()->getBlock('esto-order-cancel-info')
                        ->setOrderId($order->getIncrementId());
                    $resultPage->getConfig()->getTitle()->set(__('Order canceled'));

                    return $resultPage;
                }
            }
        }
    }
}
