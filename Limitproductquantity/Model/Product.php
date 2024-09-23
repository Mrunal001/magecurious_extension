<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Model;

class Product extends \Magento\Catalog\Model\Product
{
    const LINK_TYPE = 'customlink';
    const LINK_TYPE_CUSTOMLINK = 7;

    /**
     * Retrieve array of customlink products
     *
     * @return array
     */
    public function getCustomLinkProducts()
    {
        if (!$this->hasCustomLinkProducts()) {
            $products = [];
            $collection = $this->getCustomLinkProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setCustomLinkProducts($products);
        }
        return $this->getData('custom_link_products');
    }

    /**
     * Retrieve customlink products identifiers
     *
     * @return array
     */
    public function getCustomLinkProductIds()
    {
        if (!$this->hasCustomLinkProductIds()) {
            $ids = [];
            foreach ($this->getCustomLinkProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setCustomLinkProductIds($ids);
        }
        return [$this->getData('custom_link_product_ids')];
    }

    /**
     * Retrieve collection customlink product
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getCustomLinkProductCollection()
    {
        $collection = $this->getLinkInstance()->setLinkTypeId(
            static::LINK_TYPE_CUSTOMLINK
        )->getProductCollection()->setIsStrongMode();
        $collection->setProduct($this);

        return $collection;
    }
}
