<?php
namespace Saee\ShipmentMethod\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Saee\ShipmentMethod\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('saee_response')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('saee_response')
            )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    12,
                    ['nullable' => false, 'unsigned' => true],
                    'order_id'
                )
                ->addColumn(
                    'waybill',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false],
                    'waybill'
                    )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false],
                    'Shipment Status'
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    150,
                    ['nullable' => false],
                    'Shipment Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->setComment('Response Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('saee_response'),
                $setup->getIdxName(
                    $installer->getTable('saee_response'),
                    ['order_id','waybill','status','message'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['order_id','waybill','status','message'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

         $installer->endSetup();
    }
}

