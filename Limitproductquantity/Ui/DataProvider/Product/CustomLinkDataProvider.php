<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Ui\DataProvider\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;

class CustomLinkDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getLinkType()
    {
        return 'customlink';
    }

    /**
     * {@inheritdoc}
     *
     * @since 101.0.0
     */
    public function getCollection()
    {
        /**
        * @var Collection $collection
        */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');

        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        if (!$this->getProduct()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );

        $collection->setVisibility(
            $this->getVisibleInSiteIds()
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Return visible site ids
     *
     * @return array
     */
    private function getVisibleInSiteIds()
    {
        return [
            Visibility::VISIBILITY_IN_SEARCH,
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH
        ];
    }
}
