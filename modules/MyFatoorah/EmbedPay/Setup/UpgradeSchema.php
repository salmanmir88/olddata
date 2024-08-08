<?php

namespace MyFatoorah\EmbedPay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $conn      = $setup->getConnection();
        $tableName = $setup->getTable('myfatoorah_invoice');

        if ($conn->isTableExists($tableName) != true) {
            //for ver 3.0.0 in etc/module.xml to be ver 3.0.1 to ver 3.0.4
            $table = $conn->newTable($tableName)
                    ->addColumn(
                            'id',
                            Table::TYPE_BIGINT,
                            null,
                            ['primary' => true, 'identity' => true, 'unsigned' => true, 'nullable' => false]
                    )
                    ->addColumn(
                            'order_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => false]
                    )
                    ->addColumn(
                            'invoice_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true]
                    )
                    ->addColumn(
                            'invoice_reference',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The Invoice Reference'
                    )
                    ->addColumn(
                            'invoice_url',
                            Table::TYPE_TEXT,
                            255,
                            ['nullable' => true],
                            'The Invoice or Payment URL'
                    )
                    ->addColumn(
                            'gateway_id',
                            Table::TYPE_TEXT,
                            10,
                            ['nullable' => true, 'default' => null],
                            'The used Payment Gateway'
                    )
                    ->addColumn(
                            'reference_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The Reference ID'
                    )
                    ->addColumn(
                            'track_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The Track ID'
                    )->addColumn(
                            'authorization_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The Authorization ID'
                    )->addColumn(
                            'gateway_transaction_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The used Payment Gateway Transaction ID'
                    )
                    ->addColumn(
                            'payment_id',
                            Table::TYPE_TEXT,
                            32,
                            ['nullable' => true],
                            'The Payment ID'
                    )
                    ->addColumn(
                            'gateway_name',
                            Table::TYPE_TEXT,
                            100,
                            ['nullable' => false, 'default' => 'myfatoorah'],
                            'The used Payment Gateway - name'
                    )
                    ->setOption('charset', 'utf8');
            $conn->createTable($table);
        } else {
            //for ver 3.0.1 in etc/module.xml to be ver 3.0.2
            $conn->addColumn($tableName, 'invoice_reference', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The Invoice Reference',
            ]);
            $conn->addColumn($tableName, 'invoice_url', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'The Invoice/Payment URL',
            ]);
            $conn->addColumn($tableName, 'gateway_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 10,
                'nullable' => true,
                'default'  => null,
                'comment'  => 'The used Payment Gateway',
            ]);
            $conn->addColumn($tableName, 'reference_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The Reference ID',
            ]);
            $conn->addColumn($tableName, 'track_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The Track ID',
            ]);
            $conn->addColumn($tableName, 'authorization_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The Authorization ID',
            ]);
            $conn->addColumn($tableName, 'gateway_transaction_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The used Payment Gateway Transaction ID',
            ]);
            $conn->addColumn($tableName, 'payment_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => true,
                'comment'  => 'The Payment ID',
            ]);
            $conn->addColumn($tableName, 'gateway_name', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 100,
                'nullable' => false,
                'default'  => 'myfatoorah',
                'comment'  => 'The used Payment Gateway - name',
            ]);
            //change order_id from bigint to varchar 32
            $conn->modifyColumn($tableName, 'order_id', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 32,
                'nullable' => false,
            ]);
            $conn->modifyColumn($tableName, 'id', [
                'type'     => Table::TYPE_BIGINT,
                'primary'  => true,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false
            ]);
        }

        $setup->endSetup();
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
