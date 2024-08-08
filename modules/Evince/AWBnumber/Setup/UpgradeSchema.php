<?php
namespace Evince\AWBnumber\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $installer->getConnection()->addColumn(
                  $installer->getTable('sales_order_grid'),
                  'awb_link',
                  [
                      'type' => Table::TYPE_TEXT,
                      'nullable' => true,
                      'default' => null,
                      'length' => 255,
                      'comment' => 'AWB Link',
                  ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                  $installer->getTable('sales_order_grid'),
                  'aramex_waybill_number',
                  [
                      'type' => Table::TYPE_TEXT,
                      'nullable' => true,
                      'default' => null,
                      'length' => 255,
                      'comment' => 'Aramex AWB',
                  ]
            );
            $installer->getConnection()->addColumn(
                  $installer->getTable('sales_order'),
                  'aramex_waybill_number',
                  [
                      'type' => Table::TYPE_TEXT,
                      'nullable' => true,
                      'default' => null,
                      'length' => 255,
                      'comment' => 'Aramex AWB',
                  ]
            );
        }

        $setup->endSetup();
    }
}