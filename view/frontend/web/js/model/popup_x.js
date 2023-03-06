define(
    [
        'ko',
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function (ko, $, modal) {
        'use strict';
        return {
            popupElement: '#term_esto_x',
            popupModal: null,
            options : {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: 'Close',
                    class: '',
                    click: function() {
                        this.closeModal();
                    }
                }],
                clickableOverlay: true
            },

            openPopupTerm: function () {
                if(this.popupModal)
                    this.popupModal.openModal();
            },
            getEstoTermPopup: function() {
                this.popupModal = modal(this.options, $(this.popupElement));
            }
        }
    }
);