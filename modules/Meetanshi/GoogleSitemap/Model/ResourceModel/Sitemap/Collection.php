<?php
namespace Meetanshi\GoogleSitemap\Model\ResourceModel\Sitemap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Meetanshi\GoogleSitemap\Model\ResourceModel\Sitemap as GoogleSitemapResourceModel;
use Meetanshi\GoogleSitemap\Model\Sitemap as GoogleSitemapModel;

/**
 * Class Collection
 * @package Meetanshi\GoogleSitemap\Model\ResourceModel\Sitemap
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(GoogleSitemapModel::class, GoogleSitemapResourceModel::class);
    }

    /**
     * @param $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }
}
