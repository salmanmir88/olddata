<?php

namespace Meetanshi\GoogleSitemap\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Meetanshi\GoogleSitemap\Cron\SitemapCron;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\SitemapFactory as GoogleSitemapFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class InstallData
 * @package Meetanshi\GoogleSitemap\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var GoogleSitemapFactory
     */
    private $googleSitemapFacotry;

    /**
     * @var SitemapCron
     */
    private $sitemapCron;

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * InstallData constructor.
     * @param GoogleSitemapFactory $googleSitemapFacotry
     * @param SitemapCron $sitemapCron
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GoogleSitemapFactory $googleSitemapFacotry,
        SitemapCron $sitemapCron,
        GoogleSitemapHelper $googleSitemapHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->googleSitemapFacotry = $googleSitemapFacotry;
        $this->sitemapCron = $sitemapCron;
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->googleSitemapHelper->setConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP,
                1
            );
            $this->googleSitemapHelper->setConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_NUMBER_URL,
                50000
            );
            $this->googleSitemapHelper->setConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_FILE_SIZE,
                10485760
            );
            $data = [
                'sitemap_filename' => "sitemap.xml",
                'sitemap_path' => "/",
                'store_id' => $this->storeManager->getStore()->getId()
            ];
            $this->googleSitemapFacotry->create()->addData($data)->save();
            $this->sitemapCron->regenerateSitemaps($this->storeManager->getStore()->getId(), true);
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }
}
