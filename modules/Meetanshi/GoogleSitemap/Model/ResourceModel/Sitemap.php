<?php
namespace Meetanshi\GoogleSitemap\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Sitemap
 * @package Meetanshi\GoogleSitemap\Model\ResourceModel
 */
class Sitemap extends AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('mt_google_sitemap', 'sitemap_id');
    }
}
