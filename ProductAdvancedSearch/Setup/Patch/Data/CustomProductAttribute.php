<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class CustomProductAttribute for Create Custom Product Attribute using Data Patch.
 */
class CustomProductAttribute implements DataPatchInterface {

    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply() {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('catalog_product', 'product_brand', [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Brand',
            'input' => 'select',
            'class' => '',
            'source' => 'Magecurious\ProductAdvancedSearch\Model\Config\Source\CustomAttributeOptions',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() {
        return [];
    }
}
