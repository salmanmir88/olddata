<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Sitemap\Controller\Adminhtml\Sitemap;

/**
 * Class Index
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class Index extends Sitemap\Index
{

    /**
     * @return Sitemap\Index|void
     */
    protected function _initAction()
    {
        parent::_initAction();
        $this->_setActiveMenu("Meetanshi_GoogleSitemap::main_menu");
    }

    /**
     *
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage XML Sitemaps'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
