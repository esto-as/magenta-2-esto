<?php

namespace Esto\HirePurchase\Observer;

use Magento\Framework\Event\ObserverInterface;

class OnPaymentConfigSave implements ObserverInterface
{
    public $configWriter;
    public $structure;
    public $scopeConfig;
    public $configValueFactory;
    public $cacheTypeList;

    /** @var \Magento\Config\Model\ResourceModel\Config\Data */
    public $resource;

    /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql */
    public $connection;

    public function __construct(
        \Magento\Config\Model\Config\Structure $structure,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    )
    {
        $this->structure = $structure;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->configValueFactory = $configValueFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->resource = $this->configValueFactory->create()->getCollection()->getResource();
        $this->connection = $this->resource->getConnection();
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $childMethods = [
            'esto_x',
            'esto_pay_later',
            'esto_pay',
            'esto_pay_card'
        ];

        foreach ($childMethods as $childMethod) {
            $this->_inheritConfigValues($childMethod);
        }

        $this->cleanConfigCache();
    }

    public function cleanConfigCache()
    {
        $this->cacheTypeList->cleanType('config');
    }

    public function _inheritConfigValues($childMethod)
    {
        $childFields = [];
        foreach ($this->structure->getElement('payment/esto_hirepurchase/' . $childMethod)->getChildren() as $child) {
            $childData = $child->getData();
            if ($childData['_elementType'] == 'field') {
                $childFields[] = $child->getId();
            }
        };

        $allFields = [];
        foreach ($this->structure->getElement('payment/esto_hirepurchase')->getChildren() as $child) {
            $childData = $child->getData();
            if ($childData['_elementType'] == 'field') {
                $allFields[] = $child->getId();
            }
        };

        $clonedFields = array_diff($allFields, $childFields);

        $rowsToClone = $this->getConfigRowsToClone($clonedFields);
        $rowsToDelete = $this->getConfigRowsToClean($clonedFields, $childMethod);
        if (count($rowsToDelete)) $this->_deleteInheritedRows($rowsToDelete, $childMethod);
        //removing min_order_total for Esto pay by card
        $rowsToClone = array_reverse($rowsToClone);
        foreach ($rowsToClone as $id => $row) {
            if ($row['path'] == 'payment/esto_hirepurchase/min_order_total') {
                unset($rowsToClone[$id]);
                break;
            }
        }
        $this->_insertInheritedRows($rowsToClone, $childMethod);
    }

    public function getConfigRowsToClone($clonedFields)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $configDataCollection */
        $configDataCollection = $this->configValueFactory->create()->getCollection();
        $configDataCollection->addPathFilter('payment/esto_hirepurchase');

        $clonedFieldsExpressions = [];
        foreach ($clonedFields as $clonedField) {
            $clonedFieldsExpressions[] = "path LIKE '%/$clonedField'";
        }
        $expression = implode(' OR ', $clonedFieldsExpressions);
        $configDataCollection->getSelect()->where($expression);

        $result = $configDataCollection->toArray();
        return $result['items'];
    }

    public function getConfigRowsToClean($clonedFields, $childMethod)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $configDataCollection */
        $configDataCollection = $this->configValueFactory->create()->getCollection();
        $configDataCollection->addPathFilter('payment/' . $childMethod);

        $clonedFieldsExpressions = [];
        foreach ($clonedFields as $clonedField) {
            $clonedFieldsExpressions[] = "path LIKE '%/$clonedField'";
        }
        $expression = implode(' OR ', $clonedFieldsExpressions);
        $configDataCollection->getSelect()->where($expression);

        $result = $configDataCollection->toArray();
        return $result['items'];
    }

    public function _deleteInheritedRows($rowsToClone, $childMethod)
    {
        $whereClauses = [];
        foreach ($rowsToClone as $row) {
            $targetPath = str_replace('esto_hirepurchase', $childMethod, $row['path']);
            $whereClauses[] = sprintf("(path = '%s' AND scope = '%s' AND scope_id = %s)",
                $targetPath, $row['scope'], $row['scope_id']);
        }

        $whereClauses = implode(' OR ', $whereClauses);
        $this->connection->delete($this->resource->getMainTable(), $whereClauses);
    }

    public function _insertInheritedRows($rowsToClone, $childMethod)
    {
        $columns = [
            'scope',
            'scope_id',
            'path',
            'value'
        ];

        $data = [];
        foreach ($rowsToClone as $row) {
            $targetPath = str_replace('esto_hirepurchase', $childMethod, $row['path']);
            $data[] = [
                $row['scope'],
                $row['scope_id'],
                $targetPath,
                $row['value']
            ];
        }

        $this->connection->insertArray($this->resource->getMainTable(), $columns, $data);
    }
}