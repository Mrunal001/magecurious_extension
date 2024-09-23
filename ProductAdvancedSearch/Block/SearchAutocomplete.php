<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Block;

use Magento\Customer\Model\SessionFactory as CustomerSession;

class SearchAutocomplete extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;
    protected $_store;
    protected $customerSession;
    protected $httpContext;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Store $store,
        CustomerSession $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_store = $store;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }
    
    public function getAjaxUrl()
    {
		return $this->_storeManager->getStore()->getUrl('searchautocomplete/index/index');
	}

    public function getCustomerSession()
    {
        return $this->customerSession->create()->isLoggedIn();
    }
}
