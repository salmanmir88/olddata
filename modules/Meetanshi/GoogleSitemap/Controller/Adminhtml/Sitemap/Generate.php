<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\SitemapFactory as GoogleSitemapFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Generate
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class Generate extends Sitemap
{
    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var GoogleSitemapFactory
     */
    private $googleSitemapFactory;

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param GoogleSitemapFactory $googleSitemapFactory
     * @param Emulation $appEmulation
     * @param GoogleSitemapHelper $googleSitemapHelper
     */
    public function __construct(
        Action\Context $context,
        GoogleSitemapFactory $googleSitemapFactory,
        Emulation $appEmulation,
        GoogleSitemapHelper $googleSitemapHelper
    ) {
        parent::__construct($context);
        $this->googleSitemapFactory = $googleSitemapFactory;
        $this->appEmulation = $appEmulation;
        $this->googleSitemapHelper = $googleSitemapHelper;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $sitemap = $this->googleSitemapFactory->create();
            if (!$this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP,
                ScopeInterface::SCOPE_STORES,
                $sitemap->getStoreId()
            )) {
                $this->messageManager->addErrorMessage(
                    __('GoogleSitemap Extension is disabled please enable it to generate sitemap')
                );
                return $this->_redirect('google_sitemap/*/');
            }
            $id = $this->getRequest()->getParam('sitemap_id');

            $sitemap->load($id);
            if ($sitemap->getId()) {
                try {
                    $this->appEmulation->startEnvironmentEmulation(
                        $sitemap->getStoreId(),
                        Area::AREA_FRONTEND,
                        true
                    );
                    $result = $sitemap->generateXml($sitemap->getStoreId());
                    if ($result instanceof \Exception) {
                        throw $result;
                    }
                    $this->appEmulation->stopEnvironmentEmulation();
                    $this->messageManager->addSuccessMessage(
                        __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
                    ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
                }
            } else {
                $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
            }
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
        return $this->_redirect('google_sitemap/*/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
