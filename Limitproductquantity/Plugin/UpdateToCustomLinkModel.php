<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Plugin;

class UpdateToCustomLinkModel
{
    /**
     * @param \Magecurious\Limitproductquantity\Model\ProductFactory $catalogModel
     */
    public function __construct(
        \Magecurious\Limitproductquantity\Model\ProductFactory $catalogModel
    ) {
        $this->catalogModel = $catalogModel;
    }
    
    /**
     * Before plugin to update model class
     *
     * @param  \Magecurious\Limitproductquantity\Model\ProductLink\CollectionProvider\CustomLinkProducts $subject
     * @param  Object                                                                                    $product
     * @return array
     */
    public function beforeGetLinkedProducts(
        \Magecurious\Limitproductquantity\Model\ProductLink\CollectionProvider\CustomLinkProducts $subject,
        $product
    ) {
        $currentProduct = $this->catalogModel->create()->load($product->getId());
        return [$currentProduct];
    }
}
