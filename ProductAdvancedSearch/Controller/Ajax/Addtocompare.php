<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\Product\Compare\ListCompare;

class Addtocompare extends Action 
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $customerSession;
    protected $wishlistRepository;
    protected $formKey;
    protected $url;
    protected $redirectFactory;
    protected $messageManager;
    protected $listCompare;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        ResultFactory $resultFactory,
        FormKey $formKey,
        UrlInterface $url,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ListCompare $listCompare
    ) 
    {
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->listCompare = $listCompare;
        parent::__construct($context);
    }

    public function execute() 
    {
       $sku = $this->getRequest()->getParam('compareProduct');
        
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        $this->listCompare->addProduct($product);

        $productName = $product->getName();
        $this->messageManager->addSuccess(__('%1 has been added to your compare list.', $productName));

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => true, 'message' => 'Added to compare']);
        return $result;
    }
}
 