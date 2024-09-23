<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = 'payment/testpayment/active';
    const API_KEY = 'payment/testpayment/apikey';
    const MERCHANT_KEY = 'payment/testpayment/merchantid';

    protected $session;
    protected $quote;
    protected $quoteManagement;
    protected $orderSender;
    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->session = $session;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /*
     * @return bool
     */
    public function isEnabled()
    {
        $currentStore = $this->storeManager->getStore()->getId();

        if ($currentStore == 33)
        {
            return $this->scopeConfig->getValue(
                self::MODULE_ENABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
    }

    public function apiKey()
    {
        return $this->scopeConfig->getValue(
            self::API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function merchantKey()
    {
        return $this->scopeConfig->getValue(
            self::MERCHANT_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
