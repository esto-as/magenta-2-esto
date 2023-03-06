define(
    [
        'ko',
        'jquery'
    ],
    function (ko, $) {
        'use strict';
        return {

            /**
             * Validate all
             * @returns {boolean}
             */
            validate: function() {
                if(!this.isEstoChosen()) {
                    return true;
                }

                var $termAndConditionErrorMessage = this.getTermConditionErrorMessageElement();
                if (!$termAndConditionErrorMessage.parents(".agreement-selector").is(":visible")) return true;
                return this.validateElement(this.validateTermAndCondition(), $termAndConditionErrorMessage);
            },

            validateElement: function ($condition, $errorElement) {
                if (!$condition) {
                    $($errorElement).show();
                    return false;
                } else {
                    $($errorElement).hide();
                }
                return true;
            },

            /**
             * @returns {boolean}
             */
            validateTermAndCondition: function () {
                var $termAndConditionCheckbox = this.getTermConditionElement();
                if ($termAndConditionCheckbox.length > 0) {
                    return ($termAndConditionCheckbox.is(':checked'));
                }
            },
            getTermConditionElement: function () {
                return $('div.payment-method._active input[id^="accept_"]')
            },
            getTermConditionErrorMessageElement: function () {
                return $('div.payment-method._active div[id*="_term-error-message"]');
            },
            isEstoChosen: function() {
                var chosenMethodCode = $('input[name="payment[method]"]:checked').val();
                return (chosenMethodCode.indexOf('esto') != -1);
            }
        }
    }
);