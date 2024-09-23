<?php

/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\AbandonedCarteMail\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;

class Form extends Template
{
    protected $customerSession;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }
    /**
     * Redirects to the submit file
     */
    public function getFormAction()
    {
         return $this->getUrl('abandonedCart/quote/guestmailupdate', ['_secure' => true]);
    }

     /**
      * here we get the current customer  data
      *
      * @return this
      */
    public function getCustomerId()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getId();
        }
    }
}
