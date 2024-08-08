<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;
use Meetanshi\GoogleSitemap\Model\SitemapFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Edit
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class Edit extends Sitemap\Edit
{
    /**
     * @var SitemapFactory
     */
    private $googleSitemapFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param SitemapFactory $googleSitemapFactory
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        SitemapFactory $googleSitemapFactory,
        Session $session
    ) {
        parent::__construct($context, $coreRegistry);
        $this->googleSitemapFactory = $googleSitemapFactory;
        $this->session = $session;
    }

    /**
     *
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('sitemap_id');
            $model = $this->googleSitemapFactory->create();
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This sitemap no longer exists.'));
                    $this->_redirect('adminhtml/*/');
                    return;
                }
            }
            $data = $this->session->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            $this->_coreRegistry->register('sitemap_sitemap', $model);
            $this->_initAction()->_addBreadcrumb(
                $id ? __('Edit Sitemap') : __('New Sitemap'),
                $id ? __('Edit Sitemap') : __('New Sitemap')
            )->_addContent(
                $this->_view->getLayout()
                    ->createBlock(\Meetanshi\GoogleSitemap\Block\Adminhtml\Edit::class)
            )->_addJs(
                $this->_view->getLayout()
                    ->createBlock(Template::class)->setTemplate('Meetanshi_GoogleSitemap::js.phtml')
            )->_setActiveMenu("Meetanshi_GoogleSitemap::main_menu");
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Site Map'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                $model->getId() ? $model->getSitemapFilename() : __('New Sitemap')
            );
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
