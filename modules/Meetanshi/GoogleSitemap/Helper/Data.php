<?php

namespace Meetanshi\GoogleSitemap\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Meetanshi\GoogleSitemap\Helper
 */
class Data
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP='google_sitemap/xml_settings/xml_sitemap';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_ENABLE_HOMEPAGE_OPTIMIZATION='google_sitemap/xml_settings/enable_homepage_optimization';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_ENABLE_ADDITIONAL_LINKS='google_sitemap/xml_settings/enable_additional_links';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_ADDITIONAL_LINKS='google_sitemap/xml_settings/additional_links';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_INCLUDE_IMAGES='google_sitemap/xml_settings/include_images';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_NUMBER_URL='google_sitemap/xml_settings/maximum_number_url';
    /**
     *
     */
    const GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_FILE_SIZE='google_sitemap/xml_settings/maximum_file_size';

    /**
     *
     */
    const GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_SITEMAP_UPDATE_FREQUENCY='google_sitemap/auto_generation_settings/sitemap_update_frequency';
    /**
     *
     */
    const GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_SEND_SITEMAP_ERROR_EMAIL='google_sitemap/auto_generation_settings/send_sitemap_error_email';
    /**
     *
     */
    const GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_EMAIL_RECIPIENT='google_sitemap/auto_generation_settings/email_recipient';
    /**
     *
     */
    const GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_EMAIL_SENDER='google_sitemap/auto_generation_settings/email_sender';
    /**
     *
     */
    const GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_ERROR_EMAIL_TEMPLATE='google_sitemap/auto_generation_settings/error_email_template';

    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_HTML_SITEMAP='google_sitemap/html_settings/html_sitemap';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_CATEGORY_URL='google_sitemap/html_settings/category_url';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_PRODUCT_URL='google_sitemap/html_settings/product_url';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_CMS_PAGE_URL='google_sitemap/html_settings/cms_page_url';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_EXCLUDE_CMS_PAGES='google_sitemap/html_settings/exclude_cms_pages';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_ENABLE_ADDITIONAL_LINKS='google_sitemap/html_settings/enable_additional_links';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SETTINGS_ADDITIONAL_LINKS='google_sitemap/html_settings/additional_links';
    /**
     *
     */
    const GOOGLE_SITEMAP_HTML_SITEMAP_LINK_TO_FOOTER='google_sitemap/html_settings/sitemap_link_to_footer';


    const PRODUCT_URL_SUFFIX='catalog/seo/product_url_suffix';
    const CATEGORY_URL_SUFFIX='catalog/seo/category_url_suffix';

    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @param $path
     * @param string $scope
     * @param null $scopeId
     * @return mixed
     */
    public function getConfigData($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = null)
    {
        return $this->scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * @param $path
     * @param $value
     * @param string $scope
     * @param int $scopeId
     */
    public function setConfigData($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->configWriter->save($path, $value, $scope, $scopeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getMaximumFileSize($storeId)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getMaximumLinesNumber($storeId)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_SITEMAP_XML_SETTINGS_MAXIMUM_NUMBER_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getXslFileContent()
    {
        $sitemapURL='{$sitemapURL}';
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<xsl:stylesheet version=\"2.0\"
                xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\"
                xmlns:sitemap=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
    <xsl:output method=\"html\" version=\"1.0\" encoding=\"UTF-8\" indent=\"yes\"/>
    <xsl:template match=\"/\">
        <html>
            <head>
                <title>XML Sitemap - Magento Blog – Tutorials, Tips, News &amp; Insights | Meetanshi</title>
                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
                <style type=\"text/css\">
                    body
                    {
                    margin: 0;
                    padding: 0;
                    font-family: \"Segoe UI\";
                    }

                    table,tbody
                    {
                    margin: 0;
                    padding: 0;
                    }

                    .xml-sitemap-container, .xml-sitemap-header-container, .flwh
                    {
                    float: left;
                    width: 100%;
                    }

                    .xml-sitemap-container .xml-sitemap-header-container
                    {
                    min-height: 220px;
                    float: left;
                    width: 100%;
                    background-color: #d3e5a0;
                    text-align: center;
                    }

                    .xml-sitemap-container .sitemap-header-title
                    {
                    font-size: 32px;
                    font-weight: bold;
                    font-family: \"Segoe UI\";
                    text-decoration: underline;
                    text-transform: uppercase;
                    margin-top: 57px;
                    color: #23273f;
                    }

                    .sitemap-header-content-parent
                    {
                    float: left;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    }

                    .xml-sitemap-container .sitemap-header-content
                    {
                    margin-top: 34px;
                    font-family: Segoe UI semibold;
                    font-size: 16px;
                    color: #23273f;
                    max-width: 1054px;
                    text-align: center;
                    margin-bottom: 44px;
                    }

                    .xml-sitemap-container .sitemap-header-content a
                    {
                    color: black;
                    text-decoration: underline;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container
                    {
                    float: left;
                    width: 100%;
                    text-align: center;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-title
                    {
                    color: #896126;
                    font-size: 18px;
                    margin-top: 54px;
                    font-family: Segoe UI semibold;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-title count
                    {
                    color: black;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table-parent
                    {
                    float: left;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    {
                    overflow-x: auto;
                    max-width: 1730px;
                    margin-top: 38px;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    {
                    float: left;
                    width: 100%;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table table
                    {
                    float: left;
                    width: 100%;
                    border-collapse: collapse;
                    text-align: left;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr th
                    {
                    background-color: #23273f;
                    height: 70px;
                    color: white;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr td{height: 62px}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr th,
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr td
                    {
                    padding-left: 32px;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    tr:nth-child(odd){background-color: #f2f2f2}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr
                    th:nth-child(1){width: 70%}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr
                    th:nth-child(2){width: 30%}
                </style>
            </head>
            <body>
                <div class=\"xml-sitemap-container\">
                    <div class=\"xml-sitemap-header-container\">
                        <div class=\"sitemap-header-title\">
                            XML SITEMAP
                        </div>
                        <div class=\"sitemap-header-content-parent\">
                            <div class=\"sitemap-header-content\">
                                This XML Sitemap is generated by <a>Meetanshi's Magento 2 Google Sitemap Extension</a>.
                                It helps search
                                engines like Google, Bing to crawl and re-crawl categories/products/CMS pages/images of
                                your website.
                                Learn more about <a href=\"http://sitemaps.org\" target=\"_blank\">XML Sitemaps</a>.
                            </div>
                        </div>
                    </div>
                    <div class=\"xml-sitemap-content-container\">
                        <div class=\"sitemap-content-title\">
                            <xsl:if test=\"sitemap:sitemapindex/sitemap:sitemap\">
                                This XML Sitemap Index file Contains
                                <count><xsl:value-of select=\"count(sitemap:sitemapindex/sitemap:sitemap)\"/></count>
                                Sitemaps.
                            </xsl:if>
                            <xsl:if test=\"sitemap:urlset/sitemap:url\">
                                This XML Sitemap file Contains
                                <count><xsl:value-of select=\"count(sitemap:urlset/sitemap:url)\"/></count>
                                URLs.
                            </xsl:if>

                        </div>
                        <div class=\"sitemap-content-table-parent\">
                            <div class=\"sitemap-content-table\">
                                <table>
                                    <tr>
                                        <th>Sitemaps</th>
                                        <th>Last Modified</th>
                                    </tr>
                                    <xsl:for-each select=\"sitemap:sitemapindex/sitemap:sitemap\">
                                        <xsl:variable name=\"sitemapURL\">
                                            <xsl:value-of select=\"sitemap:loc\"/>
                                        </xsl:variable>
                                        <tr>
                                            <td>
                                                <a href=\"{$sitemapURL}\">
                                                <xsl:value-of select=\"sitemap:loc\"/>
                                                </a>
                                            </td>
                                            <td>
                                                <xsl:value-of select=\"sitemap:lastmod\"/>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                    <xsl:for-each select=\"sitemap:urlset/sitemap:url\">
                                        <xsl:variable name=\"sitemapURL\">
                                            <xsl:value-of select=\"sitemap:loc\"/>
                                        </xsl:variable>
                                        <tr>
                                            <td>
                                                <a href=\"{$sitemapURL}\">
                                                    <xsl:value-of select=\"sitemap:loc\"/>
                                                </a>
                                            </td>
                                            <td>
                                                <xsl:value-of select=\"sitemap:lastmod\"/>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>";
    }
}
