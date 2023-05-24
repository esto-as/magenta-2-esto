<?php

namespace Esto\HirePurchase\Gateway\Http\Client;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Client;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class EstoClient implements ClientInterface
{
    public function __construct(
        private ClientFactory $clientFactory,
        protected Json        $json,
        protected Logger      $logger
    )
    {}

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
        /** @var Client $request */
        $request = $this->clientFactory->create();

        $options = [];

        if ($transferObject->getAuthUsername() && $transferObject->getAuthPassword()) {
            $options['auth'] = [$transferObject->getAuthUsername(), $transferObject->getAuthPassword()];
        }

        $options['headers'] = [
            'Content-Type' => 'application/json'
        ];

        $options['body'] = $this->json->serialize($transferObject->getBody());

        try {
            $result = $request->request(
                HttpRequest::METHOD_POST,
                $transferObject->getUri(),
                $options
            )->getBody();

            $result = $this->json->unserialize($result);

            if (!$result) {
                $result = [];
                if (function_exists('json_last_error_msg')) {
                    $log['json_last_error'] = json_last_error_msg();
                } else {
                    $log['json_last_error'] = json_last_error();
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Payment\Gateway\Http\ClientException(
                __($e->getMessage())
            );
        } finally {
            $this->logger->debug($log);
        }

        return $result;
    }
}
