<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = 'limitproductquantity/general/enable';

    const ERR_MSG_QTY = 'limitproductquantity/general/errmsgqty';

    const LIMIT_FROM = 'limitproductquantity/general/startdate';

    const LIMIT_UNTIL = 'limitproductquantity/general/enddate';

    const LIMIT_MIN_QTY = 'limitproductquantity/general/minlimitqty';

    const LIMIT_MAX_QTY = 'limitproductquantity/general/maxlimitqty';

    const LIMIT_GROUP = 'limitproductquantity/general/limitcust';

    const CUSTOMER_GROUP = 'limitproductquantity/general/custgrp';
    
    public function __construct(
        Context $context,
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

    public function errMsg()
    {
        return $this->scopeConfig->getValue(
            self::ERR_MSG_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function limitFrom()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_FROM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function limitUntil()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_UNTIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function limitMinQty()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_MIN_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function limitMaxQty()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_MAX_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function limitGroup()
    {
        return $this->scopeConfig->getValue(
            self::LIMIT_GROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function customerGroup()
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_GROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
