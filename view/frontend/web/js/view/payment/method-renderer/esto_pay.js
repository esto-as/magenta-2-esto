define(
    [
        'Esto_HirePurchase/js/view/payment/method-renderer/esto_hirepurchase',
        'jquery',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (Component, $, selectPaymentMethodAction, checkoutData, additionalValidators) {
        'use strict';
        var context, message;
        return Component.extend({
            /**
             * Inits
             */
            initialize: function () {
                context = this;
                this._super();
            },

            placeOrderEsto: function () {
                if (additionalValidators.validate()) {
                    if($(".country-blocks label>div.active").hasClass('active')){
                        this.placeOrder();
                    } else {
                        context.isPlaceOrderActionAllowed(false);
                    }
                }
            },
            getBanks: function () {
                var banks = window.checkoutConfig.payment[this.getCode()].banks, country, index, bankLogos,
                    bankData, bankNames, bankNamesData = [];
                for (country in banks) {
                    bankData = [];
                    bankLogos = [];
                    bankNames = [];
                    for (index in banks[country]) {
                        bankLogos.push(banks[country][index].logo);
                        bankNames.push(banks[country][index]);
                    }
                    bankData.country = $.mage.__(country);
                    bankData.banks = bankNames;
                    bankNamesData.push(bankData);
                }
                context.isPlaceOrderActionAllowed(false);
                return bankNamesData;
            },
            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                $(".country-blocks label>div.active").removeClass('active');
                context.isPlaceOrderActionAllowed(false);

                return true;
            },
            setBank: function (data, event) {
                window.checkoutConfig.payment.esto_pay.bank = data.name;
                $(".country-blocks label>div.active").removeClass('active');
                event.target.classList.add('active');
                context.isPlaceOrderActionAllowed(true);
            }
        });
    }
);
