<?php
/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\AbandonedCarteMail\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Reports\Model\ResourceModel\Quote\CollectionFactory;
use \Magecurious\AbandonedCarteMail\Helper\Data;

class Resetstatus
{
    protected $_dataHelper;
    protected $logger;
    protected $_scopeConfig;
    protected $storeManager;
    protected $_quotesFactory;
   
    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        Data $dataHelper
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_quotesFactory = $quotesFactory;
        $this->_dataHelper = $dataHelper;
    }

    //Cron Execution Start and Reset E-mail Status

    public function execute()
    {
        if ($this->_dataHelper->isEnabled()) {
            $this->logger->info("Magecurious E-mail Status Reset Cron Job Running");

            /**
            *
             *
            * @var $collection \Magento\Reports\Model\ResourceModel\Quote\Collection
            */
            $collection = $this->_quotesFactory->create();

            $store_id = 1;
            $collection->prepareForAbandonedReport([$store_id])->addFieldToFilter('email_status', ['eq' =>'1']);
            $rows = $collection->load();

            foreach ($rows as $value) {
                $value->setEmailStatus(0);
                $value->save();
            }
        }
    }
}
