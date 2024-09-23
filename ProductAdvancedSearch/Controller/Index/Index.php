<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Controller\Index;

use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as productCollectionFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as SearchFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Reports\Model\Product\Index\Viewed;
use Magento\Reports\Model\ResourceModel\Product\Index\Viewed\CollectionFactory as viewFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute as AttributeResource;
use \Magecurious\ProductAdvancedSearch\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_dataHelper;
    protected $recentlyViewed;
    protected $_resultJsonFactory; 
    protected $_productCollectionFactory;
    protected $_reviewFactory;
    protected $_storeManager;
    protected $_imageBuilder;
    protected $_productVisibility;
    protected $_categoryFactory;
    protected $_formKey;
    protected $_priceHelper;
    protected $_urlBuilder;
    protected $_wishlistFactory;
    protected $_wishlistResource;
    protected $queryCollectionFactory;
    protected $_attributeCollectionFactory;
    protected $_urlRewriteFactory;
    protected $formKey;   
    protected $cart;
    protected $product;
    protected $customerSession;
    protected $_cookieManager;
    protected $_cookieMetadataFactory;
    protected $cookieReader;
    protected $_sessionManager;
    protected $_remoteAddressInstance;
    protected $productFactory;
    protected $categoryNames;
    protected $recentlyViewedModel;
    protected $recentlyViewedCollectionFactory;
    protected $recentProductBlock;
    protected $attributeCollectionFactory;
    protected $attributeResource;
    protected $categoryCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SearchFactory $queryCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        productCollectionFactory $productCollectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        UrlInterface $urlBuilder,
        FormKey $formKey,
        Product $product,
        CustomerSession $customerSession,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieReaderInterface $cookieReader,
        SessionManagerInterface $sessionManager,
        ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollection,
        Viewed $recentlyViewedModel,
        CollectionFactory $recentlyViewedCollectionFactory,
        \Magento\Reports\Block\Product\Viewed $recentlyViewed,
        AttributeCollectionFactory $attributeCollectionFactory,
        AttributeResource $attributeResource,
        Data $dataHelper
        
    )
    {
        parent::__construct($context);
        $this->_resultJsonFactory   =   $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;
        $this->_imageBuilder = $imageBuilder;
        $this->_productVisibility = $productVisibility;
        $this->categoryFactory = $categoryFactory;
        $this->_priceHelper = $priceHelper;
        $this->_urlBuilder = $urlBuilder;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieReader = $cookieReader;
        $this->_objectManager = $objectManager;
        $this->_sessionManager = $sessionManager;
        $this->productFactory = $productFactory;
        $this->product = $product;
        $this->categoryCollection = $categoryCollection;
        $this->recentlyViewedModel = $recentlyViewedModel;
        $this->recentlyViewedCollectionFactory = $recentlyViewedCollectionFactory;
        $this->recentlyViewed = $recentlyViewed;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeResource = $attributeResource;
        $this->_dataHelper = $dataHelper;
        $this->_remoteAddressInstance = $this->_objectManager->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
    }

    public function execute()
    {
        $postMessage = $this->getRequest()->getPost();
        $query = preg_replace('/[^A-Za-z0-9\ \_\'\-]/', '', $postMessage['query'] ?? '');

        if ($this->_dataHelper->getBrowsingHistory()) {
            $cookiePrefix = 'Browsing_History_';

            $existingCookieName = null;
            foreach ($_COOKIE as $cookieName => $cookieValue) {
                if (strpos($cookieName, $cookiePrefix) === 0 && $cookieValue === $query) {
                    $existingCookieName = $cookieName;
                    break;
                }
            }

            if (!isset($existingCookieName)) {
                $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata();
                $publicCookieMetadata->setDuration(86400);
                $publicCookieMetadata->setPath('/');
                $publicCookieMetadata->setHttpOnly(false);

                $cookieName = 'Browsing_History_' . time();

                $this->_cookieManager->setPublicCookie(
                    $cookieName,
                    $query,
                    $publicCookieMetadata
                );
            }
        }

        $BrowsingTerm = [];
        $a = 1;
        $displayedCookies = 0;

        $cookiePrefix = 'Browsing_History_';

        foreach ($_COOKIE as $cookieName => $cookieValue) {
            if (strpos($cookieName, $cookiePrefix) === 0) {
                if (isset($existingCookieName) && $cookieName === $existingCookieName) {
                    continue;
                }

                $BrowsingTerm[$a]['BrowsingHistory'] = ucfirst($this->_cookieManager->getCookie($cookieName));
                $BrowsingTerm[$a]['url'] = $cookieValue;
                $a++;
            }
        }

        // Get Popular and Recent search terms

        $queryCollection = $this->queryCollectionFactory->create();

        $queryCollection->addFieldToSelect('query_text')
                        ->addFieldToSelect('redirect')
                        ->setPopularQueryFilter()
                        ->addFieldToFilter('num_results', ['gt' => 0])
                        ->setOrder('query_id', 'desc')
                        ->setPageSize($this->_dataHelper->ShowPopularMaxLength())
                        ->setCurPage(1);

        $topSearchTerms = [];
        $j = 1;

        $FirstTerms = [];
        $m = 1;

        $FirstTerms[$m]['query'] = ucfirst($query);
        
        foreach ($queryCollection as $queryItem) {
            $topSearchTerms[$j]['top_recent_terms'] = ucfirst($queryItem->getData('query_text'));
            $topSearchTerms[$j]['top_search_terms'] = ucfirst($queryItem->getData('query_text'));
            $topSearchTerms[$j]['url'] = $queryItem->getData('redirect');
            $j++;
        }

        // Get Recently Viewed Products
            
        $recentlyViewedCollection = $this->recentlyViewed->getItemsCollection()
            ->setPageSize($this->_dataHelper->ShowRecentViewMaxLength())
            ->setCurPage(1);
        
        $recentView = [];
        $n = 1;
    
        foreach ($recentlyViewedCollection as $queryRecent) 
        {
            $recentView[$n]['url'] = $queryRecent->getProductUrl();
            $recentView[$n]['small'] = $this->getRecentImage($queryRecent, 'product_small_image')->getImageUrl();
            $recentView[$n]['namerecent'] = $queryRecent->getName();
            $recentView[$n]['sku'] = $queryRecent->getSku();
            $recentView[$n]['price'] = $this->_priceHelper->currency(number_format($queryRecent->getFinalPrice(),2),true,false);
            $n++;
        }

        //Get Search Weight

        $dynamicFieldRowJson = $this->_dataHelper->getSearchAttribute();
        $dynamicFieldRow = json_decode($dynamicFieldRowJson, true);
        $existingAttributes = [];

        foreach ($dynamicFieldRow as $rowKey => $row) {
            $attributeCode = $row['attribute_name'];
            $searchWeight = $row['dropdown_field'];

            if (in_array($attributeCode, $existingAttributes)) {
                continue;
            }

            $attributeCollection = $this->attributeCollectionFactory->create();
            $attribute = $attributeCollection->addFieldToFilter('attribute_code', $attributeCode)->getFirstItem();

            if ($attribute->getId()) {
                $attribute->setData('attribute_code', $attributeCode);
                $attribute->setData('search_weight', $searchWeight);
                $attribute->save();

                $attributeCollection->clear();
                $attributeCollection->addFieldToFilter('attribute_code', $attributeCode);
                $attribute = $attributeCollection->getFirstItem();

                $existingAttributes[] = $attributeCode;
            }
        }

        // Get Product collecion
       
        $collection = $this->_productCollectionFactory->create();

        $collection->addAttributeToSelect('*')
                            ->addAttributeToFilter(
                                [
                                    ['attribute' => 'name', 'like' => '%' . $query . '%'],
                                    ['attribute' => 'sku', 'like' => '%' . $query . '%'],
                                    ['attribute' => 'description', 'like' => '%' . $query . '%'],
                                    
                                ]
                            )
                            ->addAttributeToSort('name', 'DESC')
                            ->addAttributeToSort('description', 'ASC')
                            ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
                            ->setPageSize($this->_dataHelper->ShowProductMaxLength())
                            ->setCurPage(1);

        $productCount = $collection->getSize();
        
        $productList = [];
        $i = 1;
        
        foreach ($collection as $product)
        {
            $productList[$i]['id'] = $product->getId();
            $productList[$i]['typeid'] = $product->getTypeId();
            $productList[$i]['name'] = str_ireplace($query,'<b>'.ucfirst($query).'</b>',$product->getName());
            $productList[$i]['sku'] = $product->getSku();
            $productList[$i]['price'] = $this->_priceHelper->currency(number_format($product->getFinalPrice(),2),true,false);
            $productList[$i]['url'] = $product->getProductUrl();
            $productList[$i]['small'] = $this->getImage($product, 'product_small_image')->getImageUrl();
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
            $productList[$i]['rating'] = $product->getRatingSummary();
            
            $i++;
        }

        //Get Category Collection

        $categoryCollection = $this->categoryCollection->create()
        ->addAttributeToSelect('*')
        ->addFieldToFilter('name', ['like' => '%' . $query . '%'])
        ->setPageSize(5)
        ->setCurPage(1);
    
        $productCategoryNames = [];
        $k = 1;
    
        foreach ($categoryCollection as $category) {
            $productCategoryNames[$k]['category_names'] = $category->getName();
            $productCategoryNames[$k]['url'] = $category->getUrl();
            $k++;
        }


        $response = [
        'productlist' => $productList,
        'populersearch' => $topSearchTerms,
        'firstterm' => $FirstTerms,
        'recentview' => $recentView,
        'browsingterm' => $BrowsingTerm,
        'categorylist' => $productCategoryNames,
        'productcount' => $productCount,
        ];

        if (!empty($response)) {
            return $this->_resultJsonFactory->create()->setData($response);
        } else {
            return $this->_resultJsonFactory->create()->setData([]);
        }
    }
    
    public function getImage($product, $imageId)
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }

    public function getRecentImage($queryRecent, $imageId)
    {
        return $this->_imageBuilder->setProduct($queryRecent)
            ->setImageId($imageId)
            ->create();
    }
}
