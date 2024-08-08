<?php

namespace Meetanshi\GoogleSitemap\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Psr\Log\LoggerInterface;

/**
 * Class EmailNotification
 * @package Meetanshi\GoogleSitemap\Model
 */
class EmailNotification
{
    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * EmailNotification constructor.
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param GoogleSitemapHelper $googleSitemapHelper
     */
    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        GoogleSitemapHelper $googleSitemapHelper
    ) {
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $errors
     * @param $store_id
     */
    public function sendErrors($errors, $store_id)
    {
        $this->inlineTranslation->suspend();
        try {
            if (!(is_array($errors))) {
                $errors=[$errors];
            }
            $this->transportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue(
                    GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_ERROR_EMAIL_TEMPLATE,
                    ScopeInterface::SCOPE_STORE,
                    $store_id
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $errors)]
            )->setFrom(
                $this->scopeConfig->getValue(
                    GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_EMAIL_SENDER,
                    ScopeInterface::SCOPE_STORE,
                    $store_id
                )
            )->addTo(
                $this->scopeConfig->getValue(
                    GoogleSitemapHelper::GOOGLE_SITEMAP_AUTO_GENRERATION_SETTINGS_EMAIL_RECIPIENT,
                    ScopeInterface::SCOPE_STORE,
                    $store_id
                )
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
