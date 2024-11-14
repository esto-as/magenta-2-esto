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
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Ui/js/modal/alert',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Esto_HirePurchase/js/model/popup',
        'Esto_HirePurchase/js/model/popup_pay',
        'Esto_HirePurchase/js/model/popup_pay_later',
        'Esto_HirePurchase/js/model/popup_x',
        'Esto_HirePurchase/js/model/popup_pay_card',
        'mage/translate'
    ],
    function ($, ko, Component, alert, storage, urlBuilder, fullScreenLoader, additionalValidators, popup, popup_pay, popup_pay_later, popup_x, popup_pay_card, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Esto_HirePurchase/payment/form'
            },

            popup_object: {
                'esto_pay_later': 'popup_pay_later',
                'esto_pay': 'popup_pay',
                'esto_x': 'popup_x',
                'esto_hirepurchase': 'popup',
                'esto_pay_card': 'popup_pay_card'
            },

            months: {
                1: "January",
                2: "February",
                3: "March",
                4: "April",
                5: "May",
                6: "June",
                7: "July",
                8: "August",
                9: "September",
                10: "October",
                11:"November",
                0: "December"
            },

            selectedCountry: ko.observable(),

            setBank: function (data, event) {
                window.checkoutConfig.payment.esto_pay.bank = data.name;
                $(".country-blocks label>div.active").removeClass('active');
                event.target.classList.add('active');
            },

            getData: function () {
                if (this.item.method == "esto_pay" || window.checkoutConfig.payment.esto_pay.bank !== undefined) {
                    return {
                        'method': this.item.method,
                        'po_number': null,
                        'additional_data': {'payment_method_key': window.checkoutConfig.payment.esto_pay.bank}
                    };
                } else if (this.item.method == "esto_pay_card") {
                    return {
                        'method': this.item.method,
                        'po_number': null,
                        'additional_data': {'payment_method_key': "STRIPE_CARD"}
                    };
                } else {
                    return {
                        'method': this.item.method,
                        'po_number': null,
                        'additional_data': null
                    };
                }
            },

            placeOrderEsto: function () {
                if (additionalValidators.validate()) {
                    this.placeOrder();
                }
            },

            redirectAfterPlaceOrder: false,

            afterPlaceOrder: function () {
                storage.post(
                    'esto/redirect/getPurchaseUrl', {'bank_id': window.checkoutConfig.payment.esto_pay.bank}
                ).done(
                    function (response) {
                        if (response.success) {
                            window.location.replace(response.url)
                        } else {
                            fullScreenLoader.stopLoader();
                            alert({
                                content: response.errorMessage,
                                actions: {
                                    cancel: function () {
                                        fullScreenLoader.startLoader();
                                        window.location.assign(urlBuilder.build('checkout/cart'))
                                    }
                                }
                            });
                        }
                    }
                );
            },

            getLogoSrc: function () {
                return window.checkoutConfig.payment[this.getCode()].logo
            },

            getDescription: function () {
                return $t(window.checkoutConfig.payment[this.getCode()].description)
            },

            getEstoXCalculatorEnabled: function () {
                return window.checkoutConfig.payment['esto_x'].calculator == "1" ? 1 : 0
            },

            isEnabled: function () {
                let isEnabled = window.checkoutConfig.payment[this.getCode()].enable_terms,
                    result = 'false';

                if (typeof isEnabled === 'undefined' || isEnabled === null){
                    isEnabled = '0';
                }

                result = isEnabled === '1' ? 'true' : 'false';
                return result;
            },

            getTermsLabel: function () {
                let termsText = window.checkoutConfig.payment[this.getCode()].terms_text;
                return termsText ? termsText : $.mage.__("Agree to the terms and conditions")
            },

            getTerms: function () {
                return window.checkoutConfig.payment[this.getCode()].terms_popup
            },
            showPopupTerm: function (code) {
                if (this.popup_object[code]) {
                    return eval(this.popup_object[code]).openPopupTerm();
                }
                return popup.openPopupTerm();
            },
            initPopupTerm: function (code) {
                if (this.popup_object[code]) {
                    return eval(this.popup_object[code]).getEstoTermPopup();
                }
                return popup.getEstoTermPopup()();
            },
            getRequiredErrorMessage: function () {
                return "This is a required field.";
            },
            getCountriesList: function () {
                var countries = window.checkoutConfig.payment[this.getCode()].countries, list = [];
                countries.forEach(function (country) {
                    if (country == window.checkoutConfig.locale) {
                        list.unshift($.mage.__(country))
                    } else {
                        list.push($.mage.__(country));
                    }
                });
                return list;
            },
            isEstoPay: function () {
                return this.getCode() == "esto_pay";
            },
            isEstoX: function () {
                return this.getCode() == "esto_x";
            },
            showBanks: function () {
                return window.checkoutConfig.payment[this.getCode()].show_banks;
            },
            getClass: function () {
                if (window.checkoutConfig.payment[this.getCode()].banks_columns) {
                    return "columns"+window.checkoutConfig.payment[this.getCode()].banks_columns;
                } else {
                    return "";
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
                return bankNamesData;
            },
            getLabel: function () {
                return $.mage.__('3 interest-free payments over 3 months');
            },
            getFirstPayment: function () {
                let price = window.checkoutConfig.totalsData.total_segments.find(total => total.code == "grand_total");
                if (price && price.value) {
                    return (parseFloat(price.value)/3).toFixed(2);
                }
                return (parseFloat(window.checkoutConfig.totalsData.total_segments.find(total => total.code == "subtotal"))/3).toFixed(2);
            },
            getSecondPayment: function () {
                let price = window.checkoutConfig.totalsData.total_segments.find(total => total.code == "grand_total");
                if (price && price.value) {
                    return (parseFloat(price.value)/3).toFixed(2);
                }
                return (parseFloat(window.checkoutConfig.totalsData.total_segments.find(total => total.code == "subtotal"))/3).toFixed(2);
            },
            getThirdPayment: function () {
                let price = window.checkoutConfig.totalsData.total_segments.find(total => total.code == "grand_total");
                if (price && price.value) {
                    let monthPayments = (parseFloat(price.value)/3).toFixed(2);
                    let lastMonth = (parseFloat(price.value) - parseFloat(monthPayments)*2).toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                    return lastMonth;
                }
                return (parseFloat(window.checkoutConfig.totalsData.total_segments.find(total => total.code == "subtotal"))/3).toFixed(2);
            },
            getMonth: function (month) {
                $.mage.__("January");
                $.mage.__("February");
                $.mage.__("March");
                $.mage.__("April");
                $.mage.__("May");
                $.mage.__("June");
                $.mage.__("July");
                $.mage.__("August");
                $.mage.__("September");
                $.mage.__("October");
                $.mage.__("November");
                $.mage.__("December");
                //just init translations

                const d = new Date();
                let name = this.months[(parseInt(d.getMonth())+month)%12];
                return $.mage.__(name);
            }
        });
    }
);
