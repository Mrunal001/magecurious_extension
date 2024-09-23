<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Escaper;
use Magecurious\Limitproductquantity\Helper\Data;

class LimitQtyAddToCart implements ObserverInterface
{
    protected $checkoutSession;
    protected $messageManager;
    protected $url;
    protected $actionFlag;
    protected $customerFactory;
    protected $productRepository;
    protected $linkType;
    protected $linkFactory;
    protected $productLinkCollectionFactory;
    protected $customerSession;
    protected $dataHelper;

    public function __construct(
        Session $checkoutSession,
        UrlInterface $url,
        ManagerInterface $messageManager,
        ActionFlag $actionFlag,
        CustomerFactory $customerFactory,
        ProductRepositoryInterface $productRepository,
        Link $linkType,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        ProductLinkCollectionFactory $productLinkCollectionFactory,
        CustomerSession $customerSession,
        Escaper $escaper,
        Data $dataHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->actionFlag = $actionFlag;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->linkType = $linkType;
        $this->linkFactory = $linkFactory;
        $this->productLinkCollectionFactory = $productLinkCollectionFactory;
        $this->customerSession = $customerSession;
        $this->escaper = $escaper;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        if ($this->dataHelper->isEnabled()) {
            $currentDate = (new \DateTime())->format('Y-m-d');
            $limitFromDate = $this->dataHelper->limitFrom();
            $limitUntilDate = $this->dataHelper->limitUntil();
    
            if ($limitFromDate && $limitUntilDate) {
                if ($currentDate < $limitFromDate || $currentDate > $limitUntilDate) {
                    return;
                }
            }
    
            $quote = $this->checkoutSession->getQuote();
    
            $productInCartId = [];
            $relatedProductIds = [];
            $childProductIds = [];
            $relatedProduct = null;
    
            foreach ($quote->getAllVisibleItems() as $item) {
                $productId = $item->getProduct()->getId();
                $productInCartId[] = $productId;
    
                $productLinkCollection = $this->productLinkCollectionFactory->create();
                $productLinkCollection->addFieldToFilter('link_type_id', 7);
                $productLinkCollection->addFieldToFilter('product_id', $productId);
                $links = $productLinkCollection->getItems();
    
                foreach ($links as $linkdata) {
                    $relatedProductId = $linkdata->getLinkedProductId();
                    $relatedProductIds[$relatedProductId] = $productId;
                }
            }

            foreach ($relatedProductIds as $relatedProduct => $mainProduct) {
                $groupproduct = $this->productRepository->getById($relatedProductId);

                if ($groupproduct->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                    $associatedProducts = $groupproduct->getTypeInstance()->getAssociatedProductIds($groupproduct);
            
                    foreach ($associatedProducts as $associatedProductId) {
                        $childProductIds[] = $associatedProductId;
                    }

                    if (!empty(array_diff($childProductIds, $productInCartId))) {
                        $mainProductName = $this->productRepository->getById($mainProduct)->getName();
                        $relatedProductName = $groupproduct->getName();

                        $this->messageManager->addComplexErrorMessage(
                            'addCustomMessage',
                            [
                                'main_product_name' => $mainProductName,
                                'related_product_url' => $groupproduct->getProductUrl(),
                                'related_product_name' => $relatedProductName,
                            ]
                        );
                    }
                } else {
                    if (!in_array($relatedProduct, $productInCartId)) {
                        $mainProductName = $this->productRepository->getById($mainProduct)->getName();
                        $relatedProductName = $this->productRepository->getById($relatedProduct)->getName();
    
                        $mainProduct = $this->productRepository->getById($mainProduct);
                        $relatedProduct = $this->productRepository->getById($relatedProduct);
    
                        $this->messageManager->addComplexErrorMessage(
                            'addCustomMessage',
                            [
                                'main_product_name' => $mainProductName,
                                'related_product_url' => $relatedProduct->getProductUrl(),
                                'related_product_name' => $relatedProductName,
                            ]
                        );
                        
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    }
                }
            }

            //Product Specific
           
            $quote = $this->checkoutSession->getQuote();
    
            foreach ($quote->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                $productQty = $item->getQty();

                if (in_array($productId, $childProductIds)) {
                        continue;
                } else {
                    if ($productId == $relatedProduct) {
                        continue;
                    }
                }
        
                $product = $this->productRepository->getById($productId);
        
                $minQtyLimit = $product->getData('min_qty');
                $maxQtyLimit = $product->getData('max_qty');
    
                if ($minQtyLimit !== null && $maxQtyLimit !== null) {
                    if ($productQty < $minQtyLimit || $productQty > $maxQtyLimit) {
                        $message1 = "You can buy min $minQtyLimit";
                        $message2 = "and max $maxQtyLimit";
                        $errorMsg = __("$message1 $message2 qty");
                        $this->messageManager->addErrorMessage($errorMsg);
        
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    }
                }
            }

            // Specific Customer
        
            if ($this->dataHelper->limitGroup() == "specificcustomers" && $this->customerSession->isLoggedIn()) {
                $quote = $this->checkoutSession->getQuote();
                foreach ($quote->getAllVisibleItems() as $item) {
                    $productId = $item->getProductId();
                    $productQty = $item->getQty();

                    if (in_array($productId, $childProductIds)) {
                        continue;
                    } else {
                        if ($productId == $relatedProduct) {
                            continue;
                        }
                    }
                            
                    $customer = $quote->getCustomer();
                    $customerId = $customer->getId();
                    $customerModel = $this->customerFactory->create()->load($customerId);
        
                    $customerMinQty = $customerModel->getData('min_qty');
                    $customerMaxQty = $customerModel->getData('max_qty');

                    if ($customerMinQty !== null && $customerMaxQty !== null) {
                        if ($productQty < $customerMinQty || $productQty > $customerMaxQty) {
                            $message1 = "You can buy min $customerMinQty";
                            $message2 = "and max $customerMaxQty";
                            $errorMsg = __("$message1 $message2 qty");
                            $this->messageManager->addErrorMessage($errorMsg);
                
                            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                
                            $controllerAction = $observer->getControllerAction();
                            $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                            return;
                        }
                    }
                }
            }

            // Customer Group Specific

            $optionarray = [];
            $helpervalue = $this->dataHelper->customerGroup();

            $customerGroupId = null;
           
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomer()->getGroupId();
            } else {
                $customerGroupId = 0;
            }
           
            if (!empty($helpervalue)) {
                $optionarray = explode(',', $helpervalue);
            }

            if ($this->dataHelper->limitGroup() == "customergroups"  && (in_array($customerGroupId, $optionarray) || !$this->customerSession->isLoggedIn())) {
                $quote = $this->checkoutSession->getQuote();
                
                foreach ($quote->getAllVisibleItems() as $item) {
                    $productId = $item->getProductId();
                    $productQty = $item->getQty();

                    if (in_array($productId, $childProductIds)) {
                        continue;
                    } else {
                        if ($productId == $relatedProduct) {
                            continue;
                        }
                    }

                    if (($helpervalue == 0) && ($customerGroupId == 0) && ($productQty < $this->dataHelper->limitMinQty() || $productQty > $this->dataHelper->limitMaxQty())) {
                        $this->custommessage();
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    } elseif ($helpervalue == 1 && $customerGroupId == 1) {
                        $this->custommessage();
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    } elseif ($helpervalue == 2 && $customerGroupId == 2) {
                        $this->custommessage();
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    } elseif ($helpervalue == 3 && $customerGroupId == 3) {
                        $this->custommessage();
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $controllerAction = $observer->getControllerAction();
                        $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
                        return;
                    }
                }
            }
        }
    }

    public function custommessage()
    {
        $minqty = $this->dataHelper->limitMinQty();
        $maxqty = $this->dataHelper->limitMaxQty();
        $message1 = "You can buy min $minqty";
        $message2 = "and max $maxqty";
        $errorMsg = __("$message1 $message2 qty");
        $this->messageManager->addErrorMessage($errorMsg);
    }
}
