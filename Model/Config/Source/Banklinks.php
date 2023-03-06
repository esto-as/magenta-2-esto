<?php

namespace Esto\HirePurchase\Model\Config\Source;

class Banklinks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 2, 'label' => 'Yes open by default'],
            ['value' => 1, 'label' => 'Yes closed by default'],
            ['value' => 0, 'label' => 'No']
        ];
    }
}
