/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magecurious_Juspay/js/action/set-payment-method-action'
    ],
    function (ko, $, Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    redirectAfterPlaceOrder: false,
                    template: 'Magecurious_Juspay/payment/testpayment'
                },
                afterPlaceOrder: function () {
                    setPaymentMethodAction(this.messageContainer);
                    return false;
                },
                /**
                 * Returns send check to info
                 */
                getMailingAddress: function () {
                    return window.checkoutConfig.payment.customtemplate.mailingAddress;
                }
            }
        );
    }
);
