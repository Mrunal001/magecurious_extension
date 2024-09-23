<?php

/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\AbandonedCarteMail\Cron;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Escaper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Reports\Model\ResourceModel\Quote\CollectionFactory;
use \Magecurious\AbandonedCarteMail\Helper\Data;

class Abandoned
{
    protected $_dataHelper;
    protected $logger;
    protected $transportBuilder;
    protected $escaper;
    protected $inlineTranslation;
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
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        Data $dataHelper
    ) {
        $this->logger = $logger;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_quotesFactory = $quotesFactory;
        $this->_dataHelper = $dataHelper;
    }

    //Cron Execution Start and Send Mail to Customer

    public function execute()
    {
        if ($this->_dataHelper->isEnabled() && $this->_dataHelper->isGuestEnabled() == 1) {
            $this->logger->info("Magecurious Abandoned Cart cron Job Running");

            /** @var $collection \Magento\Reports\Model\ResourceModel\Quote\Collection */
            $collection = $this->_quotesFactory->create();

            $store_id = 38;
            $collection->prepareForAbandonedReport([$store_id])->addFieldToFilter('email_status', ['eq' =>'0']);
            $rows = $collection->load();

            try {
                $this->inlineTranslation->suspend();

                $sentToEmail = $this->_scopeConfig ->getValue('abandonedcartemail/general/email_sender', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $templateId = $this->_scopeConfig ->getValue('abandonedcartemail/general/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sentToName = $this->_scopeConfig ->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                
                $sender = [
                    'name' => $sentToName,
                    'email' => $sentToEmail
                ];

                foreach ($rows as $value) {
                    $quoteId = $value->getId();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $quoteFactory =  $objectManager->create('\Magento\Quote\Model\QuoteRepository');
                    $quote = $quoteFactory->get($quoteId);
                    $items = $quote->getItems();
                    $transport = $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars([
                        'customer_name'  => $value->getCustomerFirstname(),
                        'items' => $items,
                    ])
                    ->setFrom($sender)
                    ->addTo($value->getCustomerEmail())
                    ->getTransport();
                    $transport->sendMessage();
                
                    $value->setEmailStatus(1);
                    $value->save();
                }

                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
