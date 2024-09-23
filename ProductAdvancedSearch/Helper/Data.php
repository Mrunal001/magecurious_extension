<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
 
class Data extends AbstractHelper
{
    const XML_MODULE_ENABLE = 'productadvancedsearch/general/enable';

    const XML_POPULAR_ENABLE = 'productadvancedsearch/popular_search/search_popular';

    const XML_RECENT_ENABLE = 'productadvancedsearch/recent_search/recentsearches';

    const XML_ENABLE_CUSTOM_LAYOUT = 'productadvancedsearch/customlayout/enabled_custom';

    const XML_GET_TEXT_COLOR = 'productadvancedsearch/customlayout/text';

    const XML_GET_BACKGROUND_COLOR = 'productadvancedsearch/customlayout/background';

    const XML_GET_BORDER_COLOR = 'productadvancedsearch/customlayout/border';

    const XML_GET_HOVER_COLOR = 'productadvancedsearch/customlayout/hover';

    const XML_PATH_AUTOCOMPLETE_WIDTH = 'productadvancedsearch/general/popupwidth';

    const CONFIG_MIN_QUERY_LENGTH = 'productadvancedsearch/general/mincharacter';

    const XML_SEARCH_POPUP_DELAY = 'productadvancedsearch/general/popupdelay';

    const XML_SEARCH_POPULAR_SEARCH_TITLE = 'productadvancedsearch/popular_search/title_search';

    const XML_SEARCH_POPULAR_SEARCH_LENGTH = 'productadvancedsearch/popular_search/maxresult_search';

    const XML_SEARCH_RECENT_SEARCH_TITLE = 'productadvancedsearch/recent_search/title_recent';

    const XML_SEARCH_SHOW_SKU = 'productadvancedsearch/product/showsku';

    const XML_SEARCH_SHOW_REVIEW = 'productadvancedsearch/product/showreview';

    const XML_SEARCH_SHOW_ADDTOCART = 'productadvancedsearch/product/addtocart';

    const XML_SEARCH_CATEGORY_RESULT = 'productadvancedsearch/product/maxresult';

    const XML_SEARCH_ENABLE_CATEGORY = 'productadvancedsearch/category/category_section';

    const XML_SEARCH_CATEGORY_TITLE_SHOW = 'productadvancedsearch/category/title_category';

    const XML_SEARCH_RECENT_VIEW = 'productadvancedsearch/recent/enabled_recently';

    const XML_SEARCH_RECENT_VIEW_MAX_RESULT = 'productadvancedsearch/recent/maxresult_recent';

    const XML_SEARCH_RECENTLY_TITLE = 'productadvancedsearch/recent/title_recent';

    const XML_SEARCH_BROWSING_HISTORY_TITLE = 'productadvancedsearch/browsing_history/title_browsing';

    const XML_SEARCH_SEARCH_WEIGHT = 'productadvancedsearch/search/attribute';

    const XML_SEARCH_BROWSING_HISTORY_ENABLE = 'productadvancedsearch/browsing_history/search_browsing';

    
    
    protected $encryptor;
    
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    )
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }
 
    /*
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_MODULE_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

     /*
     * @return bool
     */
    public function isPopularEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_POPULAR_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

      /*
     * @return bool
     */
    public function isRecentEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_RECENT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * @return bool
     */
    public function isEnabledCustomLayout()
    {
        return $this->scopeConfig->getValue(
            self::XML_ENABLE_CUSTOM_LAYOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTextColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_GET_TEXT_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBackgroundColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_GET_BACKGROUND_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBorderColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_GET_BORDER_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getHoverColor()
    {
        return $this->scopeConfig->getValue(
            self::XML_GET_HOVER_COLOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAutocompleteWidth()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AUTOCOMPLETE_WIDTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMinCharacter()
    {
        return (int) $this->scopeConfig->getValue(
            self::CONFIG_MIN_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPopupDelay()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_SEARCH_POPUP_DELAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPopularSearchTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_POPULAR_SEARCH_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getRecentSearchTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_RECENT_SEARCH_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowSKU()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_SEARCH_SHOW_SKU,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowReview()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_SEARCH_SHOW_REVIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowAddToCart()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_SEARCH_SHOW_ADDTOCART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowCategories()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_ENABLE_CATEGORY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowCategorieTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_CATEGORY_TITLE_SHOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowProductMaxLength()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_CATEGORY_RESULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowPopularMaxLength()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_POPULAR_SEARCH_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowRecentView()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_RECENT_VIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ShowRecentViewMaxLength()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_RECENT_VIEW_MAX_RESULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getRecentViewTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_RECENTLY_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBrowsingHistoryTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_BROWSING_HISTORY_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBrowsingHistory()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_BROWSING_HISTORY_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSearchAttribute()
    {
        return $this->scopeConfig->getValue(
            self::XML_SEARCH_SEARCH_WEIGHT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
