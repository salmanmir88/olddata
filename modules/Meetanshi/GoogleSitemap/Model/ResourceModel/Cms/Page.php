<?php
namespace Meetanshi\GoogleSitemap\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetUtilityPageIdentifiersInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;

/**
 * Class Page
 * @package Meetanshi\GoogleSitemap\Model\ResourceModel\Cms
 */
class Page extends \Magento\Sitemap\Model\ResourceModel\Cms\Page
{
    /**
     * @var
     */
    protected $metadataPool;

    /**
     * @var
     */
    protected $entityManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * Page constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param null $connectionName
     * @param GetUtilityPageIdentifiersInterface|null $getUtilityPageIdentifiers
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        ScopeConfigInterface $scopeConfig,
        GoogleSitemapHelper $googleSitemapHelper,
        $connectionName = null,
        GetUtilityPageIdentifiersInterface $getUtilityPageIdentifiers = null
    ) {
        parent::__construct(
            $context,
            $metadataPool,
            $entityManager,
            $connectionName,
            $getUtilityPageIdentifiers
        );
        $this->scopeConfig = $scopeConfig;
        $this->googleSitemapHelper = $googleSitemapHelper;
    }

    /**
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier NOT IN (?)',
            $this->getUtilityPageIdentifiers($storeId)
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $pages = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getUtilityPageIdentifiers($storeId)
    {
        $homePageIdentifier = $this->scopeConfig->getValue(
            'web/default/cms_home_page',
            ScopeInterface::SCOPE_STORE
        );
        $noRouteIdentifier  = $this->scopeConfig->getValue(
            'web/default/cms_no_route',
            ScopeInterface::SCOPE_STORE
        );

        $noCookieIdentifier = $this->scopeConfig->getValue(
            'web/default/cms_no_cookies',
            ScopeInterface::SCOPE_STORE
        );
        if ($this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_ENABLE_HOMEPAGE_OPTIMIZATION,
            ScopeInterface::SCOPE_STORES,
            $storeId
        )) {
            return [$homePageIdentifier,$noRouteIdentifier, $noCookieIdentifier];
        } else {
            return [$noRouteIdentifier, $noCookieIdentifier];
        }
    }
}
