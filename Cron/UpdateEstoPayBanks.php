<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Cron;

use Esto\HirePurchase\Model\BankLinks\BankRepository;
use Esto\HirePurchase\Model\PaymentMethods\Provider;
use Esto\HirePurchase\Model\ResourceModel\Bank as BankResource;

class UpdateEstoPayBanks
{
    /** @var \Esto\HirePurchase\Model\PaymentMethods\Provider */
    private $bankProvider;

    /** @var \Esto\HirePurchase\Model\BankLinks\BankRepository */
    private $bankRepository;

    /** @var \Esto\HirePurchase\Model\ResourceModel\Bank */
    private $bankResource;

    /**
     * BankManagement constructor.
     *
     * @param \Esto\HirePurchase\Model\PaymentMethods\Provider $bankProvider
     * @param \Esto\HirePurchase\Model\BankLinks\BankRepository $bankRepository
     * @param \Esto\HirePurchase\Model\ResourceModel\Bank $bankResource
     */
    public function __construct(
        Provider $bankProvider,
        BankRepository $bankRepository,
        BankResource $bankResource
    ) {
        $this->bankProvider = $bankProvider;
        $this->bankRepository = $bankRepository;
        $this->bankResource = $bankResource;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $banksData = $this->bankProvider->provide();
        $bankIds = [];
        foreach ($banksData as $countryCode => $banks) {
            foreach ($banks as $bankName => $logo) {
                $bank = $this->bankRepository->getBankByNameAndCountryCode($bankName, $countryCode);
                if ($bank->getEntityId() && $bank->getBankLogo() === $logo && $bank->getStatus() == 1) {
                    $bankIds[] = $bank->getEntityId();
                    continue;
                }
                $bank->setBankName($bankName);
                $bank->setBankLogo($logo);
                $bank->setCountryCode($countryCode);
                $bank->setStatus('1');
                $bank = $this->bankRepository->save($bank);
                $bankIds[] = $bank->getEntityId();
            }
        }
        if ($bankIds) {
            $this->bankResource->disableBanks($bankIds);
        }

        return true;
    }
}
