<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = 'customerdiscount/general/enable';
    const MODULE_SECOND_TIME = 'customerdiscount/general/is_again_discount_give';
    const DISCOUNT_TITLE = 'customerdiscount/general/discount_title';

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
 
    /*
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::MODULE_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * @return bool
     */
    public function isAgainEnabled()
    {
        return $this->scopeConfig->getValue(
            self::MODULE_SECOND_TIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    // get custom discount from config
    public function getCustomDiscountTitle()
    {
        return $this->scopeConfig->getValue(
            self::DISCOUNT_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
