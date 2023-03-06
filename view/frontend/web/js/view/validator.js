define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Esto_HirePurchase/js/model/validator'
    ],
    function (Component, additionalValidators, termsValidator) {
        'use strict';
        additionalValidators.registerValidator(termsValidator);
        return Component.extend({});
    }
);