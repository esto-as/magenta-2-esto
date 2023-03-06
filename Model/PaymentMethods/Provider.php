<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\PaymentMethods;

use Esto\HirePurchase\Helper\ConfigProvider;

/**
 * Class Provider
 *
 * @package Esto\HirePurchase\Model\PaymentMethods
 */
class Provider
{
    /** @var string */
    const SERVICE = 'purchase';

    /** @var \Esto\HirePurchase\Model\Ui\ConfigProvider */
    private $configProvider;

    /**
     * Provider constructor.
     *
     * @param \Esto\HirePurchase\Helper\ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function provide()
    {
        $paymentsDataFromApi = $this->loadPaymentsData();

        return $this->prepareBanksData($paymentsDataFromApi);
    }

    /**
     * @param $paymentsData
     * @return array
     */
    private function prepareBanksData($paymentsData)
    {
        $banksData = [];
        foreach ($paymentsData as $countryCode => $paymentsDataByCountry) {
            foreach ($paymentsDataByCountry as $payment) {
                if ($payment->schedule_type !== 'ESTO_PAY') {
                    continue;
                }
                $banksData[$countryCode][$payment->key] = $payment->logo_url;
            }
        }

        return $banksData;
    }

    /**
     * @return array
     */
    private function loadPaymentsData()
    {
        $token = $this->getToken();
        $mode = $this->isTestMode();
        $countries = $this->getAvailableCountries();
        $payments = [];
        foreach ($countries as $countryCode) {
            $payments[$countryCode] = $this->getAvailablePayments($countryCode, $token, $mode);
        }

        return $payments;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        $userName = $this->configProvider->getUserName();
        $password = $this->configProvider->getPassword();

        return base64_encode($userName . ':' . $password);
    }

    /**
     * @return bool
     */
    private function isTestMode()
    {
        $mode = $this->configProvider->getMode();

        return $mode === "test" ? true : false;
    }

    /**
     * @return array
     */
    private function getAvailableCountries(): array
    {
        $countries = explode(',', $this->configProvider->getSpecificCountries());

        return $countries;
    }

    /**
     * @return string
     */
    private function getApiUrl(): string
    {
        return $this->configProvider->getApiUrl();
    }

    /**
     * @param $countryCode
     * @param $token
     * @param $mode
     * @return mixed
     */
    private function getAvailablePayments($countryCode, $token, $mode)
    {
        $url = $this->getApiUrl() . self::SERVICE;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . '/payment-methods?country_code=' . $countryCode . '&test_mode=' . $mode);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . $token
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $auth = curl_exec($curl);
        curl_close($curl);

        return json_decode($auth);
    }
}
