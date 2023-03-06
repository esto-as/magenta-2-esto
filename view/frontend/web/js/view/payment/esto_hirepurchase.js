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

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'esto_hirepurchase',
                component: 'Esto_HirePurchase/js/view/payment/method-renderer/esto_hirepurchase'
            },
            {
                type: 'esto_pay_later',
                component: 'Esto_HirePurchase/js/view/payment/method-renderer/esto_pay_later'
            },
            {
                type: 'esto_x',
                component: 'Esto_HirePurchase/js/view/payment/method-renderer/esto_x'
            },
            {
                type: 'esto_pay',
                component: 'Esto_HirePurchase/js/view/payment/method-renderer/esto_pay'
            },
            {
                type: 'esto_pay_card',
                component: 'Esto_HirePurchase/js/view/payment/method-renderer/esto_pay_card'
            }
        );

        return Component.extend({});
    }
);
