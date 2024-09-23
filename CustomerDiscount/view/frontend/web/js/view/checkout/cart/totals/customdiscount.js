define(
    [
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,quote,totals,priceUtils) {
        "use strict";
        return Component.extend(
            {
                defaults: {
                    template: 'Magecurious_CustomerDiscount/checkout/cart/totals/customdiscount'
                },

                totals: quote.getTotals(),
                isDisplayedCustomdiscountTotal : function () {
                    return true;
                },
                // getCustomdiscountTotal : function () {
                    
                //     var price = window.checkoutConfig.custom_data_checkout;
                //     return this.getFormattedPrice(price);
                // },

                getCustomdiscountTotal: function () {

                    var storeid = window.checkoutConfig.storeCode;
                    console.log(storeid);

                    // Check if the STORE_ID variable is defined and equal to 28
                    if (storeid == "customerdiscount") {
                        // Get the custom_data_checkout value
                        var price = window.checkoutConfig.custom_data_checkout;
                
                        // Return the formatted price
                        return this.getFormattedPrice(price);
                    }
                },

                discountTitle : function () {
                    return window.checkoutConfig.discount_title;
                }
        
            }
        );
    }
);
