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

namespace Esto\HirePurchase\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class EstoClient implements ClientInterface
{
    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * {inheritdoc}
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri()
        ];
        $result = [];
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData(json_encode($transferObject->getBody(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'application/json');
        $client->setUrlEncodeBody(true);
        $client->setUri($transferObject->getUri());

        if ($transferObject->getAuthUsername() && $transferObject->getAuthPassword()) {
            $client->setAuth($transferObject->getAuthUsername(), $transferObject->getAuthPassword());
        }

        try {
            $response = $client->request();
            $result = json_decode($response->getBody(), true);
            if (!$result) {
                $result = [];
                if (function_exists('json_last_error_msg')) {
                    $log['json_last_error'] = json_last_error_msg();
                } else {
                    $log['json_last_error'] = json_last_error();
                }
            }
            $log['response'] = $result;
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Magento\Payment\Gateway\Http\ClientException(
                __($e->getMessage())
            );
        } finally {
            $this->logger->debug($log);
        }

        return $result;
    }
}
