/*jshint jquery:true*/
define(
    [
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'uiComponent'
    ],
    function ($, url, alert, urlBuilder, quote, order, customerData,customer, fullScreenLoader, formBuilder,  storage, errorProcessor) {
        'use strict';
        return function (messageContainer) {

            var customurl = url.build('juspay/index/session');

            $.ajax(
                {
                    url: customurl,
                    type: 'POST',
                    data: {isAjax: 1},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success && response.payment_link) {
                            var paymentLink = response.payment_link;
                            window.location.href = paymentLink;
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function (response) {
                    
                    }
                }
            );
        }
    }
);