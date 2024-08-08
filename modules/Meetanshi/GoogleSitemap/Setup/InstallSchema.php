<?php

namespace Meetanshi\GoogleSitemap\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Psr\Log\LoggerInterface;

/**
 * Class InstallSchema
 * @package Meetanshi\GoogleSitemap\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        try {
            $installer = $setup;
            $installer->startSetup();
            if (!$installer->tableExists('mt_google_sitemap')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mt_google_sitemap'))
                    ->addColumn(
                        'sitemap_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary' => true,
                            'unsigned' => true,
                        ],
                        'ID'
                    )->addColumn(
                        'sitemap_type',
                        Table::TYPE_TEXT,
                        32,
                        ['nullable' => true],
                        'Sitemap Type'
                    )->addColumn(
                        'sitemap_filename',
                        Table::TYPE_TEXT,
                        32,
                        ['nullable' => true],
                        'Sitemap Filename'
                    )->addColumn(
                        'sitemap_path',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'Sitemap Path'
                    )->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false,'unsigned' => true,'default' => 0],
                        'Store ID'
                    )->addColumn(
                        'sitemap_time',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Sitemap Time'
                    )->addForeignKey(
                        $installer->getFkName('mt_google_sitemap', 'store_id', 'store', 'store_id'),
                        'store_id',
                        $installer->getTable('store'),
                        'store_id',
                        Table::ACTION_CASCADE
                    );
                $installer->getConnection()->createTable($table);

                $installer->getConnection()->addIndex(
                    $installer->getTable('mt_google_sitemap'),
                    $setup->getIdxName(
                        $installer->getTable('mt_google_sitemap'),
                        ['sitemap_path','sitemap_filename','sitemap_type'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['sitemap_path','sitemap_filename','sitemap_type'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                );
            }

            $installer->endSetup();
        } catch (\Exception $ex) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($ex->getMessage());
        }
    }
}
