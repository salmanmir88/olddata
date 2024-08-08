<?php

namespace Evince\HomeProducts\Setup;

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
                    \Magento\Catalog\Model\Category::ENTITY, 'show_on_homepage', [
                'type' => 'int',
                'label' => 'Show On Homepage',
                'input' => 'select',
                'sort_order' => 333,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'General Information',
                'backend' => ''
                    ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY, 'is_featured', [
                'type' => 'int',
                'label' => 'Featured Category',
                'input' => 'select',
                'sort_order' => 334,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'group' => 'General Information',
                'backend' => ''
                    ]
            );
        }
    }

}
