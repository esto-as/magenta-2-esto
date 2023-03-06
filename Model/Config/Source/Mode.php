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

namespace Esto\HirePurchase\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'live', 'label' => 'Live'],
            ['value' => 'test', 'label' => 'Test']
        ];
    }
}
