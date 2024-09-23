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

class Addtowishlist extends Action 
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $customerSession;
    protected $wishlistRepository;
    protected $formKey;
    protected $url;
    protected $redirectFactory;
    protected $messageManager;

    public function __construct(
    Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
    JsonFactory $resultJsonFactory,
    ProductRepositoryInterface $productRepository,
    ResultFactory $resultFactory,
    FormKey $formKey,
    UrlInterface $url,
    RedirectFactory $redirectFactory,
    ManagerInterface $messageManager
        ) {
        $this->customerSession = $customerSession;
        $this->wishlistRepository= $wishlistRepository;
        $this->productRepository = $productRepository;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute() 
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if(!$customerId) {
            $this->messageManager->addErrorMessage('Customer not logged in.');
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $sku = $this->getRequest()->getParam('productId');
        
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);

        $wishlist->addNewItem($product);
        $wishlist->save();

        $productName = $product->getName();
        $this->messageManager->addSuccess(__('%1 has been added to your wishlist.', $productName));

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => true, 'message' => 'Added to wishlist']);
        return $result;
    }
}
