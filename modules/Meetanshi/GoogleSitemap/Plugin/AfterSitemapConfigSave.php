<?php

namespace Meetanshi\GoogleSitemap\Plugin;

use Magento\Config\Model\Config;
use Magento\Sitemap\Helper\Data as XmlSitemapHelper;
use Meetanshi\GoogleSitemap\Cron\SitemapCron;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Psr\Log\LoggerInterface;

/**
 * Class AfterSitemapConfigSave
 * @package Meetanshi\GoogleSitemap\Plugin
 */
class AfterSitemapConfigSave
{
    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * @var XmlSitemapHelper
     */
    private $xmlSitemapHelper;

    /**
     * @var SitemapCron
     */
    private $sitemapCron;

    /**
     * AfterSitemapConfigSave constructor.
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param XmlSitemapHelper $xmlSitemapHelper
     * @param SitemapCron $sitemapCron
     */
    public function __construct(
        GoogleSitemapHelper $googleSitemapHelper,
        XmlSitemapHelper $xmlSitemapHelper,
        SitemapCron $sitemapCron
    ) {
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->xmlSitemapHelper = $xmlSitemapHelper;
        $this->sitemapCron = $sitemapCron;
    }

    /**
     * @param Config $subject
     * @param Config $result
     * @return Config
     */
    public function afterSave(
        Config $subject,
        Config $result
    ) {
        try {

            if (!($result->getSection() === 'google_sitemap')) {
                return $result;
            }
            if (!$this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP,
                $result->getScope(),
                $result->getScopeId()
            )) {
                return $result;
            }

            $htmlEnable=$this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_HTML_SITEMAP,
                $result->getScope(),
                $result->getScopeId()
            );

            $linkToFooter=$this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SITEMAP_LINK_TO_FOOTER,
                $result->getScope(),
                $result->getScopeId()
            );

            if (!($htmlEnable && $linkToFooter)) {
                $this->googleSitemapHelper->setConfigData(
                    GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SITEMAP_LINK_TO_FOOTER,
                    0,
                    $result->getScope(),
                    $result->getScopeId()
                );
            }

            $this->sitemapCron->regenerateSitemaps();

            return $result;
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } finally {
            return $result;
        }
    }
}
