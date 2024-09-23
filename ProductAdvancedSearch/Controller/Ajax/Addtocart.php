<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Controller\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\Http;

class Addtocart extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $cart;
    protected $formKey;
    protected $url;
    protected $redirectFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        FormKey $formKey,
        UrlInterface $url,
        RedirectFactory $redirectFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {   
        $sku = $this->getRequest()->getParam('sku');
        $quantity = $this->getRequest()->getParam('qty');
        $typeid = $this->getRequest()->getParam('typeid');

        $product = $this->productRepository->get($sku);

        if (!$product) {
            $response = $this->resultJsonFactory->create();
            $response->setData(['success' => false, 'error' => 'Product not found']);
            return $response;
        }

        $params = [
            'product' => $product->getId(),
            'qty' => $quantity,
            'form_key' => $this->formKey->getFormKey(),
        ];

        try {
            $this->cart->addProduct($product, $params);
            $this->cart->save();
        } catch (\Exception $e) {
        }

        if ($product->getTypeId() === 'configurable') 
        {
            $productUrl = $product->getUrlKey();
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $redirectUrl = $baseUrl . $productUrl . ".html";
            $response = $this->resultJsonFactory->create();
            $response->setData(['success' => true, 'redirect_url' => $redirectUrl]);
            return $response;
        }

        $response = $this->resultJsonFactory->create();
        $response->setData(['success' => true]);
        return $response;
    }
}
