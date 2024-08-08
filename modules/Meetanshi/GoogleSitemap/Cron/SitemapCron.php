<?php

namespace Meetanshi\GoogleSitemap\Cron;

use DateInterval;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\EmailNotification as SitemapEmail;
use Meetanshi\GoogleSitemap\Model\ResourceModel\Sitemap\CollectionFactory as GoogleSitemapCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class SitemapCron
 * @package Meetanshi\GoogleSitemap\Cron
 */
class SitemapCron
{
    /**
     * @var GoogleSitemapCollectionFactory
     */
    private $googleSitemapCollectionFactory;
    /**
     * @var SitemapEmail
     */
    private $emailNotification;
    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var
     */
    private $tbl_core_config_data;
    /**
     * @var
     */
    private $today;
    /**
     * @var array
     */
    private $considered_store_ids = [];
    /**
     * @var array
     */
    private $considered_website_ids = [];
    /**
     *
     */
    const UPDATE_FREQUENCY_CONFIG_PATH = GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_SITEMAP_UPDATE_FREQUENCY;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var
     */
    private $connection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * SitemapCron constructor.
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param GoogleSitemapCollectionFactory $googleSitemapCollectionFactory
     * @param SitemapEmail $emailNotification
     * @param ResourceConnection $resource
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GoogleSitemapHelper $googleSitemapHelper,
        GoogleSitemapCollectionFactory $googleSitemapCollectionFactory,
        SitemapEmail $emailNotification,
        ResourceConnection $resource,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        StoreManagerInterface $storeManager
    ) {
        $this->googleSitemapCollectionFactory = $googleSitemapCollectionFactory;
        $this->emailNotification = $emailNotification;
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->resource = $resource;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->storeManager = $storeManager;
    }

    public function scheduledGenerateSitemaps()
    {
        try {
            if (!$this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP
            )
            ) {
                return;
            }
            $this->connection = $this->resource->getConnection();
            $this->tbl_core_config_data = $this->resource->getTableName('core_config_data');
            $sql = "Select updated_at FROM `" . $this->tbl_core_config_data .
                "` where path='" . self::UPDATE_FREQUENCY_CONFIG_PATH . "'";
            $result = $this->connection->fetchAll($sql);
            if (!(is_array($result) && sizeof($result))) {
                return;
            }

            $this->today = date_create(date("Y-m-d"));

            $this->checkStoreUpdateFrequency();
            $this->checkWebsiteUpdateFrequency();
            $this->checkDefaultUpdateFrequency();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }

    public function checkStoreUpdateFrequency()
    {
        $sql = "Select scope_id,updated_at FROM `" . $this->tbl_core_config_data .
            "` where path='" . self::UPDATE_FREQUENCY_CONFIG_PATH . "' and
                scope='" . ScopeInterface::SCOPE_STORES . "'";
        $result = $this->connection->fetchAll($sql);
        $store_ids = [];
        foreach ($result as $value) {
            $scope_id = $value['scope_id'];
            $frequencyDays = $this->googleSitemapHelper->getConfigData(
                self::UPDATE_FREQUENCY_CONFIG_PATH,
                ScopeInterface::SCOPE_STORES,
                $scope_id
            );
            if (!$frequencyDays) {
                continue;
            }
            if (!is_numeric($frequencyDays)) {
                continue;
            }
            $this->considered_store_ids[] = $scope_id;

            $difference = $this->getDateDifference($value['updated_at'], $this->today);
            if ($difference->days % $frequencyDays == 0) {
                $store_ids = $value['scope_id'];
            }
        }
        if (is_array($store_ids) && sizeof($store_ids)) {
            $this->regenerateSitemaps($store_ids);
        }
    }

    public function checkWebsiteUpdateFrequency()
    {
        $sql = "Select scope_id,updated_at FROM `" . $this->tbl_core_config_data .
            "` where path='" . self::UPDATE_FREQUENCY_CONFIG_PATH . "' and
                scope='" . ScopeInterface::SCOPE_WEBSITES . "'";
        $result = $this->connection->fetchAll($sql);

        foreach ($result as $value) {
            $website_id = $value['scope_id'];
            $this->considered_website_ids[] = $website_id;
            $website_store_ids = $this->getStoreIds($website_id);
            $frequencyDays = $this->googleSitemapHelper->getConfigData(self::UPDATE_FREQUENCY_CONFIG_PATH, ScopeInterface::SCOPE_WEBSITES, $website_id);
            if (!$frequencyDays) {
                continue;
            }
            if (!is_numeric($frequencyDays)) {
                continue;
            }

            $difference = $this->getDateDifference($value['updated_at'], $this->today);
            $store_ids = [];
            if ($difference->days % $frequencyDays == 0) {
                $store_ids = array_filter($website_store_ids, function ($store_id) {
                    return !in_array($store_id, $this->considered_store_ids);
                });
            }
            if (is_array($store_ids) && sizeof($store_ids)) {
                $this->regenerateSitemaps($store_ids);
            }
        }
    }

    public function checkDefaultUpdateFrequency()
    {
        $sql = "Select scope_id,updated_at FROM `" . $this->tbl_core_config_data .
            "` where path='" . self::UPDATE_FREQUENCY_CONFIG_PATH . "' and
                scope='" . ScopeConfigInterface::SCOPE_TYPE_DEFAULT . "'";
        $result = $this->connection->fetchAll($sql);
        $frequencyDays = $this->googleSitemapHelper->getConfigData(self::UPDATE_FREQUENCY_CONFIG_PATH);
        if (!$frequencyDays) {
            return;
        }
        if (!is_numeric($frequencyDays)) {
            return;
        }
        $difference = $this->getDateDifference($result[0]['updated_at'], $this->today);
        if ($difference->days % $frequencyDays == 0) {
            if (!in_array($this->getDefaultWebsiteId(), $this->considered_website_ids)) {
                $store_ids=$this->getStoreIds($this->getDefaultWebsiteId());
                if (is_array($store_ids) && sizeof($store_ids)) {
                    $this->regenerateSitemaps($store_ids);
                }
            }
        }
    }

    /**
     * @param $websiteId
     * @return array
     */
    public function getStoreIds($websiteId)
    {
        $storeId = [];
        try {
            $storeId = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        } catch (\Exception $exception) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($exception->getMessage());
        }
        return $storeId;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return DateInterval|false
     */
    private function getDateDifference($fromDate, $toDate)
    {
        $fromDate = date_create($fromDate)->format('Y-m-d');
        $fromDate = date_create($fromDate);
        return date_diff($fromDate, $toDate);
    }

    /**
     * @return int
     */
    public function getDefaultWebsiteId()
    {
        return $this->storeManager->getDefaultStoreView()->getWebsiteId();
    }

    /**
     * @param array $store_ids
     * @param bool $isInstalling
     */
    public function regenerateSitemaps($store_ids = [], $isInstalling = false)
    {
        try {
            if (is_array($store_ids) && sizeof($store_ids)) {
                $collection = $this->googleSitemapCollectionFactory->create()->addFieldToFilter("store_id", $store_ids);
            } else {
                $collection = $this->googleSitemapCollectionFactory->create();
            }
            foreach ($collection as $sitemap) {
                try {
                    $result = $sitemap->generateXml($sitemap->getStoreId(), $isInstalling);
                    if ($result instanceof \Exception) {
                        throw $result;
                    }
                } catch (\Exception $e) {
                    if ($this->googleSitemapHelper->getConfigData(
                        GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_SEND_SITEMAP_ERROR_EMAIL,
                        ScopeInterface::SCOPE_STORES,
                        $sitemap->getStoreId()
                    )
                    ) {
                        $recipient = $this->googleSitemapHelper->getConfigData(
                            GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_EMAIL_RECIPIENT,
                            ScopeInterface::SCOPE_STORES,
                            $sitemap->getStoreId()
                        );
                        if ($recipient) {
                            $this->emailNotification->sendErrors($e->getMessage(), $sitemap->getStoreId());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }
}
