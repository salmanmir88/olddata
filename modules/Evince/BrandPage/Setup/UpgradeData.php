<?php

namespace Evince\BrandPage\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpgradeData implements UpgradeDataInterface {

    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY, 'show_on_brandpage', [
                'type' => 'int',
                'label' => 'Show On BrandPage',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'backend' => ''
                    ]
            );
        
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY, 'brand_image', [
                'type' => 'varchar',
                'label' => 'Brand Image',
                'input' => 'image',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    
                ]
            );
        }
    }

}
