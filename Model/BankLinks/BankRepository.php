<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model\BankLinks;

use Esto\HirePurchase\Model\ResourceModel\Bank as BankResource;
use Esto\HirePurchase\Model\BankLinks\Data\BankFactory as BankDataObjectFactory;
use Esto\HirePurchase\Model\ResourceModel\Bank\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Esto\HirePurchase\Api\Data\BankInterface;
use Esto\HirePurchase\Api\BankRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

class BankRepository implements BankRepositoryInterface
{
    /** @var \Esto\HirePurchase\Model\ResourceModel\Bank */
    private $bankResource;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor */
    private $dataObjectProcessor;

    /** @var \Esto\HirePurchase\Model\BankFactory */
    private $bankFactory;

    /** @var BankDataObjectFactory */
    private $bankDataObjectFactory;

    /** @var DataObjectHelper */
    private $dataObjectHelper;

    /** @var \Esto\HirePurchase\Model\ResourceModel\Bank\CollectionFactory */
    private $bankCollectionFactory;

    /**
     * BankRepository constructor.
     *
     * @param \Esto\HirePurchase\Model\ResourceModel\Bank $bankResource
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Esto\HirePurchase\Model\BankLinks\BankFactory $bankFactory
     * @param \Esto\HirePurchase\Model\BankLinks\Data\BankFactory $bankDataObjectFactory
     * @param \Esto\HirePurchase\Model\ResourceModel\Bank\CollectionFactory $bankCollectionFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        BankResource $bankResource,
        DataObjectProcessor $dataObjectProcessor,
        BankFactory $bankFactory,
        BankDataObjectFactory $bankDataObjectFactory,
        CollectionFactory $bankCollectionFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->bankResource = $bankResource;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->bankFactory = $bankFactory;
        $this->bankDataObjectFactory = $bankDataObjectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->bankCollectionFactory = $bankCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(BankInterface $bankDataObject)
    {
        if (!$bankDataObject->hasDataChanges()) {
            return null;
        }

        $bankData = $this->getBankData($bankDataObject);
        /** @var Bank $bank */
        $bank = $this->bankFactory->create();
        $bank->setData($bankData);
        $this->bankResource->save($bank);

        return $bank;
    }

    /**
     * @inheritDoc
     */
    public function getList()
    {
        return $this->bankCollectionFactory->create();
    }

    /**
     * @param \Esto\HirePurchase\Api\Data\BankInterface $bankDataObject
     * @return array
     */
    private function getBankData(BankInterface $bankDataObject)
    {
        $bankData = $this->dataObjectProcessor
            ->buildOutputDataArray($bankDataObject, BankInterface::class);

        return $bankData;
    }

    /**
     * @inheritDoc
     */
    public function getBankByNameAndCountryCode(string $bankName, string $countryCode): BankInterface
    {
        /** @var Bank $bank */
        $bank = $this->bankFactory->create();
        $this->bankResource->loadByNameAndCountryCode($bank, $bankName, $countryCode);
        if (!$bank->getId()) {
            return $this->bankDataObjectFactory->create();
        }
        $dataBank = $this->getDataModel($bank);
        $dataBank->initOrigData();

        return $dataBank;
    }

    /**
     * @param \Esto\HirePurchase\Model\BankLinks\Bank $bank
     * @return \Esto\HirePurchase\Api\Data\BankInterface
     */
    private function getDataModel(Bank $bank)
    {
        /** @var BankInterface $dataBank */
        $dataBank = $this->bankDataObjectFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataBank,
            $bank->getData(),
            BankInterface::class
        );

        return $dataBank;
    }
}
