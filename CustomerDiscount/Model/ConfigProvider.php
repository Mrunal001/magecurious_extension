<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Model\Quote;
use Magecurious\CustomerDiscount\Model\Total\Quote\CustomerDiscount;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total;
use \Magecurious\CustomerDiscount\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{
    protected $checkoutSession;
    protected $total;
    protected $_priceCurrency;
    protected $quoteTotal;
    protected $_dataHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerDiscount $total,
        PriceCurrencyInterface $priceCurrency,
        Total $quoteTotal,
        Data $dataHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->total = $total;
        $this->_priceCurrency = $priceCurrency;
        $this->quoteTotal = $quoteTotal;
        $this->storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
    }

    public function getConfig()
    {
        $helper = $this->_dataHelper;
        $extraFeeConfig = [];
        $minimumOrderAmount = 0;
        $quote = $this->checkoutSession->getQuote();
        $discountValue = $this->total->discountValue($quote, $this->quoteTotal);

        if ($discountValue !== null && is_numeric($discountValue)) {
            $formattedValue = number_format((float) $discountValue, 2, ".", "");
        } else {
            $formattedValue = $discountValue;
        }
        $currentStore = $this->storeManager->getStore()->getId();

      

        if ($currentStore == 28)
        {
            if ($formattedValue !== "") {
                $quote = $this->checkoutSession->getQuote();
                $subtotal = $quote->getSubtotal();
                $extraFeeConfig["custom_data_checkout"] = $formattedValue;
            }
            if ($helper->isEnabled()) {
                $extraFeeConfig["discount_title"] = $helper->getCustomDiscountTitle();
            }
        }
        
        return $extraFeeConfig;
    }
}
