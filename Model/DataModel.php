<?php
declare(strict_types=1);

namespace Esto\HirePurchase\Model;

use Esto\HirePurchase\Api\DataInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\SimpleDataObjectConverter as SimpleDataObjectConverterAlias;
use Magento\Framework\Stdlib\ArrayUtils;

class DataModel extends AbstractSimpleObject implements DataInterface
{
    /** @var ArrayUtils */
    private $arrayUtils;

    /** @var array */
    private $origData;

    /**
     * @inheritdoc
     */
    public function __construct(ArrayUtils $arrayUtils, array $data = [])
    {
        $this->arrayUtils = $arrayUtils;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    private function _getData(): array
    {
        $data = [];
        foreach (get_class_methods($this) as $getterMethod) {
            if (strpos($getterMethod, 'get') === 0) {
                $field = substr($getterMethod, strlen('get'));
                $field = SimpleDataObjectConverterAlias::camelCaseToSnakeCase($field);
                $data[$field] = $this->$getterMethod();
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function initOrigData()
    {
        $this->origData = $this->_getData();
    }

    /**
     * @inheritdoc
     */
    public function getOrigData()
    {
        return $this->origData;
    }

    /**
     * @inheritdoc
     */
    public function hasDataChanges(): bool
    {
        return !$this->origData || (bool)$this->arrayUtils->recursiveDiff($this->origData, $this->_getData());
    }
}
